<?php
declare(strict_types=1);

/**
 * Reserves an advisor for a conversation. The transaction and row lock make
 * selecting an available advisor safe when several users ask at once.
 *
 * @return array{asesor: array{id: int, nombre: string, telefono: string, enlace_whatsapp: ?string}, conversation_id: int, reused: bool}|null
 */
function asignarAsesor(PDO $pdo, string $telefono, ?int $afiliadoId): ?array
{
    // A second "6" from the same person can arrive while the first is being
    // processed. This short MySQL advisory lock serializes that phone only.
    // MySQL lock names are limited to 64 bytes; SHA-1 keeps this at 56 bytes.
    $lockName = 'coomeva_advisor_' . sha1($telefono);
    $getLock = $pdo->prepare('SELECT GET_LOCK(:lock_name, 5) AS acquired');
    $getLock->execute(['lock_name' => $lockName]);
    $lock = $getLock->fetch();

    if (!$lock || (int) $lock['acquired'] !== 1) {
        throw new RuntimeException('No fue posible iniciar la asignación de asesor.');
    }

    try {
        $pdo->beginTransaction();
        // A retry from the same channel must receive its already reserved advisor.
        $existing = $pdo->prepare(
            "SELECT c.id AS conversation_id, a.id, a.nombre, a.telefono, a.enlace_whatsapp
             FROM conversaciones c
             INNER JOIN asesores a ON a.id = c.asesor_id
             WHERE c.telefono = :telefono
               AND c.estado IN ('esperando_asesor', 'asesor')
             ORDER BY c.id DESC
             LIMIT 1
             FOR UPDATE"
        );
        $existing->execute(['telefono' => $telefono]);
        $assigned = $existing->fetch();

        if ($assigned) {
            $pdo->commit();
            return [
                'asesor' => normalizarAsesor($assigned),
                'conversation_id' => (int) $assigned['conversation_id'],
                'reused' => true,
            ];
        }

        $available = $pdo->query(
            "SELECT id, nombre, telefono, enlace_whatsapp
             FROM asesores
             WHERE activo = 1
               AND estado = 'disponible'
             ORDER BY ultimo_asignado ASC, id ASC
             LIMIT 1
             FOR UPDATE"
        );
        $advisor = $available->fetch();

        if (!$advisor) {
            $pdo->commit();
            return null;
        }

        $reserve = $pdo->prepare(
            "UPDATE asesores
             SET estado = 'ocupado', ultimo_asignado = NOW()
             WHERE id = :id AND activo = 1 AND estado = 'disponible'"
        );
        $reserve->execute(['id' => (int) $advisor['id']]);

        if ($reserve->rowCount() !== 1) {
            throw new RuntimeException('No fue posible reservar el asesor.');
        }

        $conversation = $pdo->prepare(
            "INSERT INTO conversaciones (telefono, afiliado_id, asesor_id, estado)
             VALUES (:telefono, :afiliado_id, :asesor_id, 'asesor')"
        );
        $conversation->execute([
            'telefono' => $telefono,
            'afiliado_id' => $afiliadoId,
            'asesor_id' => (int) $advisor['id'],
        ]);

        $conversationId = (int) $pdo->lastInsertId();
        $pdo->commit();

        return [
            'asesor' => normalizarAsesor($advisor),
            'conversation_id' => $conversationId,
            'reused' => false,
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    } finally {
        $releaseLock = $pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
        $releaseLock->execute(['lock_name' => $lockName]);
    }
}

function liberarConversacion(PDO $pdo, int $conversationId): bool
{
    $pdo->beginTransaction();

    try {
        $select = $pdo->prepare(
            "SELECT asesor_id
             FROM conversaciones
             WHERE id = :id AND estado IN ('esperando_asesor', 'asesor')
             FOR UPDATE"
        );
        $select->execute(['id' => $conversationId]);
        $conversation = $select->fetch();

        if (!$conversation || empty($conversation['asesor_id'])) {
            $pdo->commit();
            return false;
        }

        $release = $pdo->prepare(
            "UPDATE asesores
             SET estado = 'disponible'
             WHERE id = :id AND activo = 1"
        );
        $release->execute(['id' => (int) $conversation['asesor_id']]);

        $close = $pdo->prepare(
            "UPDATE conversaciones
             SET estado = 'cerrada', cerrado_en = NOW()
             WHERE id = :id"
        );
        $close->execute(['id' => $conversationId]);

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function liberarConversacionPorTelefono(PDO $pdo, string $telefono): bool
{
    $find = $pdo->prepare(
        "SELECT id
         FROM conversaciones
         WHERE telefono = :telefono AND estado IN ('esperando_asesor', 'asesor')
         ORDER BY id DESC
         LIMIT 1"
    );
    $find->execute(['telefono' => $telefono]);
    $conversation = $find->fetch();

    return $conversation ? liberarConversacion($pdo, (int) $conversation['id']) : false;
}

/** @param array<string, mixed> $advisor */
function normalizarAsesor(array $advisor): array
{
    return [
        'id' => (int) $advisor['id'],
        'nombre' => (string) $advisor['nombre'],
        'telefono' => (string) $advisor['telefono'],
        'enlace_whatsapp' => !empty($advisor['enlace_whatsapp']) ? (string) $advisor['enlace_whatsapp'] : null,
    ];
}
