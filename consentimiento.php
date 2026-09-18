<?php
declare(strict_types=1);

const VERSION_TERMINOS = '2026-09-15';
const VERSION_TRATAMIENTO_DATOS = '2026-09-15';

function tieneConsentimientoWeb(): bool
{
    return ($_SESSION['terminos_version'] ?? null) === VERSION_TERMINOS
        && ($_SESSION['datos_version'] ?? null) === VERSION_TRATAMIENTO_DATOS
        && !empty($_SESSION['consentimiento_id']);
}

function exigirConsentimientoWeb(): void
{
    if (!tieneConsentimientoWeb()) {
        header('Location: index.php');
        exit;
    }
}

function registrarConsentimientoWeb(PDO $pdo): void
{
    $sessionHash = hashIdentificadorPrivado('session|' . session_id());
    $ipHash = hashIdentificadorPrivado('ip|' . clientIpAddress());

    $stmt = $pdo->prepare(
        "INSERT INTO consentimientos_web
            (session_hash, ip_hash, terminos_version, datos_version, aceptado_en)
         VALUES
            (:session_hash, :ip_hash, :terminos_version, :datos_version, NOW())
         ON DUPLICATE KEY UPDATE
            ip_hash = VALUES(ip_hash),
            terminos_version = VALUES(terminos_version),
            datos_version = VALUES(datos_version),
            aceptado_en = NOW()"
    );
    $stmt->execute([
        'session_hash' => $sessionHash,
        'ip_hash' => $ipHash,
        'terminos_version' => VERSION_TERMINOS,
        'datos_version' => VERSION_TRATAMIENTO_DATOS,
    ]);

    $_SESSION['consentimiento_id'] = $sessionHash;
    $_SESSION['terminos_version'] = VERSION_TERMINOS;
    $_SESSION['datos_version'] = VERSION_TRATAMIENTO_DATOS;
    $_SESSION['consentimiento_aceptado_en'] = time();
}

function asociarConsentimientoAIdentificacion(PDO $pdo, string $cedula, ?int $afiliadoId): void
{
    if (empty($_SESSION['consentimiento_id'])) {
        return;
    }

    $stmt = $pdo->prepare(
        'UPDATE consentimientos_web
         SET documento_hash = :documento_hash, afiliado_id = :afiliado_id
         WHERE session_hash = :session_hash'
    );
    $stmt->execute([
        'documento_hash' => hashIdentificadorPrivado('documento|' . $cedula),
        'afiliado_id' => $afiliadoId,
        'session_hash' => $_SESSION['consentimiento_id'],
    ]);
}

function retirarConsentimientoWeb(): void
{
    unset(
        $_SESSION['consentimiento_id'],
        $_SESSION['terminos_version'],
        $_SESSION['datos_version'],
        $_SESSION['consentimiento_aceptado_en'],
        $_SESSION['cedula'],
        $_SESSION['cedula_consultada'],
        $_SESSION['afiliado_id'],
        $_SESSION['nombre'],
        $_SESSION['conversation_id']
    );
    session_regenerate_id(true);
}

function hashIdentificadorPrivado(string $value): string
{
    $pepper = (string) appConfigValue('security', 'rate_limit_pepper', '');
    if (isProduction() && strlen($pepper) < 32) {
        throw new RuntimeException('No existe una clave de protección de datos configurada.');
    }

    return hash_hmac('sha256', $value, $pepper);
}
