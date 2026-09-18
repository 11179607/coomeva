<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';

requirePostWithCsrf();
exigirConsentimientoWeb();
require __DIR__ . '/conexion.php';

$cedula = trim((string) ($_POST['cedula'] ?? ''));
if (!preg_match('/^\d{6,15}$/', $cedula)) {
    http_response_code(422);
    exit('Ingresa una cédula válida.');
}

if (!identificationRateLimit($pdo)) {
    http_response_code(429);
    header('Retry-After: 900');
    exit('Por seguridad, espera unos minutos antes de intentar nuevamente.');
}

$stmt = $pdo->prepare(
    'SELECT id, cedula, nombres, apellidos FROM afiliados WHERE cedula = :cedula AND activo = 1 LIMIT 1'
);
$stmt->execute(['cedula' => $cedula]);
$afiliado = $stmt->fetch();

if ($afiliado) {
    asociarConsentimientoAIdentificacion($pdo, $cedula, (int) $afiliado['id']);
    session_regenerate_id(true);
    $_SESSION['cedula'] = $afiliado['cedula'];
    $_SESSION['afiliado_id'] = $afiliado['id'];
    $_SESSION['nombre'] = trim($afiliado['nombres'] . ' ' . $afiliado['apellidos']);
    header('Location: afiliado.php');
    exit;
}

asociarConsentimientoAIdentificacion($pdo, $cedula, null);
session_regenerate_id(true);
$_SESSION['cedula_consultada'] = $cedula;
header('Location: no_afiliado.php');
exit;
