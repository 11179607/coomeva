<?php
declare(strict_types=1);

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../asesores/asignacion.php';
require_once __DIR__ . '/enviar_mensaje.php';
require_once __DIR__ . '/menu.php';

function procesarMensajeWhatsApp(array $payload): void
{
    foreach ($payload['entry'] ?? [] as $entry) {
        foreach ($entry['changes'] ?? [] as $change) {
            foreach ($change['value']['messages'] ?? [] as $message) {
                $telefono = (string) ($message['from'] ?? '');
                $texto = trim((string) ($message['text']['body'] ?? ''));
                $messageId = (string) ($message['id'] ?? '');

                if ($telefono === '' || $messageId === '' || !registrarEventoWhatsApp($messageId, $telefono)) {
                    continue;
                }

                try {
                    $respuesta = preg_match('/^\d{6,15}$/', $texto)
                        ? procesarCedulaWhatsApp($telefono, $texto)
                        : procesarTextoWhatsApp($telefono, $texto);

                    if (!enviarWhatsApp($telefono, $respuesta)) {
                        throw new RuntimeException('No fue posible enviar la respuesta de WhatsApp.');
                    }

                    marcarEventoWhatsAppProcesado($messageId);
                } catch (Throwable $e) {
                    error_log('Error procesando mensaje de WhatsApp: ' . $e->getMessage());
                    // The event stays pending. Meta can safely retry it because advisor allocation is idempotent per phone.
                    throw $e;
                }
            }
        }
    }
}

function procesarCedulaWhatsApp(string $telefono, string $cedula): string
{
    global $pdo;

    if (!whatsappIdentificationRateLimit($pdo, $telefono)) {
        return "Por seguridad, espera unos minutos antes de intentar otra identificación.";
    }

    $stmt = $pdo->prepare(
        'SELECT id, cedula, nombres, apellidos FROM afiliados WHERE cedula = :cedula AND activo = 1 LIMIT 1'
    );
    $stmt->execute(['cedula' => $cedula]);
    $afiliado = $stmt->fetch();

    if ($afiliado) {
        $nombre = trim($afiliado['nombres'] . ' ' . $afiliado['apellidos']);
        guardarSesionWhatsApp($telefono, $cedula, (int) $afiliado['id'], 'afiliado', 'menu_afiliado');

        return "👋 ¡Bienvenido, {$nombre}!\n\nHemos validado tu información correctamente.\n\n"
            . menuWhatsApp('afiliado');
    }

    guardarSesionWhatsApp($telefono, $cedula, null, 'no_afiliado', 'menu_no_afiliado');

    return "👋 ¡Hola!\n\nNo encontramos una afiliación asociada al documento ingresado.\n\n"
        . menuWhatsApp('no_afiliado');
}

function procesarTextoWhatsApp(string $telefono, string $texto): string
{
    $sesion = obtenerSesionWhatsApp($telefono);
    if (!$sesion) {
        return "👋 Bienvenido al asistente virtual de Coomeva.\n\nPor favor escribe tu número de cédula para comenzar.";
    }

    $comando = normalizarComandoWhatsApp($texto);
    $tipoUsuario = (string) $sesion['tipo_usuario'];
    $estadoMenu = $tipoUsuario === 'afiliado' ? 'menu_afiliado' : 'menu_no_afiliado';

    if (in_array($comando, ['CERRAR', 'FINALIZAR', 'TERMINAR'], true)) {
        if ((string) $sesion['estado'] !== 'asesor') {
            return "No tienes una atención con asesor activa.\n\n" . menuWhatsApp($tipoUsuario);
        }

        liberarAsesorWhatsApp($telefono, $estadoMenu);
        return "✅ Tu atención con asesor fue cerrada y el asesor quedó disponible para otra persona.\n\n"
            . menuWhatsApp($tipoUsuario);
    }

    if (in_array($comando, ['MENU', 'MENÚ', '0', 'INICIO'], true)) {
        if ((string) $sesion['estado'] === 'asesor') {
            return "Aún tienes un asesor asignado. Cuando la atención termine escribe CERRAR para liberarlo.\n\n"
                . menuWhatsApp($tipoUsuario);
        }

        actualizarEstadoSesionWhatsApp($telefono, $estadoMenu);
        return menuWhatsApp($tipoUsuario);
    }

    if ((string) $sesion['estado'] === 'asesor') {
        return "👨‍💼 Ya tienes un asesor asignado. Cuando finalices escribe CERRAR para liberar su disponibilidad.\n\n"
            . "También puedes escribir MENU para volver a consultar las opciones.";
    }

    if ($tipoUsuario === 'afiliado') {
        if ($comando === '6') {
            return asignarAsesorWhatsApp($telefono, !empty($sesion['afiliado_id']) ? (int) $sesion['afiliado_id'] : null);
        }

        $respuesta = respuestaMenuAfiliado($comando);
        return $respuesta ?? "No reconocí esa opción.\n\n" . menuWhatsApp('afiliado');
    }

    if (in_array($comando, ['4', '5'], true)) {
        return asignarAsesorWhatsApp($telefono, null);
    }

    $respuesta = respuestaMenuNoAfiliado($comando);
    return $respuesta ?? "No reconocí esa opción.\n\n" . menuWhatsApp('no_afiliado');
}

function asignarAsesorWhatsApp(string $telefono, ?int $afiliadoId): string
{
    global $pdo;

    $assignment = asignarAsesor($pdo, $telefono, $afiliadoId);
    if (!$assignment) {
        actualizarEstadoSesionWhatsApp($telefono, 'esperando_asesor');
        return "⏳ En este momento todos nuestros asesores están ocupados. Intenta nuevamente en unos minutos o escribe MENU para ver las opciones.";
    }

    actualizarEstadoSesionWhatsApp($telefono, 'asesor');
    $asesor = $assignment['asesor'];
    $enlace = $asesor['enlace_whatsapp'] ?: 'https://wa.me/' . preg_replace('/\D+/', '', $asesor['telefono']);

    return "👨‍💼 Te asignamos a *{$asesor['nombre']}*.\n\n"
        . "Puedes continuar con tu asesor aquí: {$enlace}\n\n"
        . "Cuando la atención termine, escribe CERRAR en este chat para liberar su disponibilidad.";
}

function liberarAsesorWhatsApp(string $telefono, string $estadoMenu): void
{
    global $pdo;
    liberarConversacionPorTelefono($pdo, $telefono);
    actualizarEstadoSesionWhatsApp($telefono, $estadoMenu);
}

function obtenerSesionWhatsApp(string $telefono): ?array
{
    global $pdo;
    $stmt = $pdo->prepare(
        'SELECT telefono, afiliado_id, tipo_usuario, estado FROM sesiones_whatsapp WHERE telefono = :telefono LIMIT 1'
    );
    $stmt->execute(['telefono' => $telefono]);
    return $stmt->fetch() ?: null;
}

function actualizarEstadoSesionWhatsApp(string $telefono, string $estado): void
{
    global $pdo;
    $stmt = $pdo->prepare(
        'UPDATE sesiones_whatsapp SET estado = :estado, ultima_interaccion = NOW() WHERE telefono = :telefono'
    );
    $stmt->execute(['telefono' => $telefono, 'estado' => $estado]);
}

function registrarEventoWhatsApp(string $messageId, string $telefono): bool
{
    global $pdo;

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO whatsapp_eventos (message_id, telefono) VALUES (:message_id, :telefono)'
        );
        $stmt->execute(['message_id' => $messageId, 'telefono' => $telefono]);
        return true;
    } catch (PDOException $e) {
        if ($e->getCode() !== '23000') {
            throw $e;
        }

        $status = $pdo->prepare('SELECT procesado_en FROM whatsapp_eventos WHERE message_id = :message_id');
        $status->execute(['message_id' => $messageId]);
        $event = $status->fetch();

        // A previous attempt that did not finish is safe to retry. Finished events are ignored.
        return $event && $event['procesado_en'] === null;
    }
}

function marcarEventoWhatsAppProcesado(string $messageId): void
{
    global $pdo;
    $stmt = $pdo->prepare('UPDATE whatsapp_eventos SET procesado_en = NOW() WHERE message_id = :message_id');
    $stmt->execute(['message_id' => $messageId]);
}

function guardarSesionWhatsApp(string $telefono, string $cedula, ?int $afiliadoId, string $tipo, string $estado): void
{
    global $pdo;

    $stmt = $pdo->prepare(
        "INSERT INTO sesiones_whatsapp (telefono, cedula, afiliado_id, tipo_usuario, estado, ultima_interaccion)
         VALUES (:telefono, :cedula, :afiliado_id, :tipo_usuario, :estado, NOW())
         ON DUPLICATE KEY UPDATE
           cedula = VALUES(cedula),
           afiliado_id = VALUES(afiliado_id),
           tipo_usuario = VALUES(tipo_usuario),
           estado = VALUES(estado),
           ultima_interaccion = NOW()"
    );

    $stmt->execute([
        'telefono' => $telefono,
        'cedula' => $cedula,
        'afiliado_id' => $afiliadoId,
        'tipo_usuario' => $tipo,
        'estado' => $estado,
    ]);
}
