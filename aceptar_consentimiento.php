<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';

requirePostWithCsrf();

if (($_POST['acepta_terminos'] ?? '') !== 'si' || ($_POST['acepta_datos'] ?? '') !== 'si') {
    retirarConsentimientoWeb();
    header('Location: espera_asesor.php');
    exit;
}

require __DIR__ . '/conexion.php';

try {
    session_regenerate_id(true);
    registrarConsentimientoWeb($pdo);
} catch (Throwable $e) {
    error_log('No fue posible registrar el consentimiento: ' . $e->getMessage());
    http_response_code(500);
    exit('No fue posible registrar tu autorización. Inténtalo nuevamente.');
}

header('Location: index.php');
exit;
