<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';

requirePostWithCsrf();

if (!empty($_SESSION['conversation_id'])) {
    require __DIR__ . '/conexion.php';
    require __DIR__ . '/asesores/asignacion.php';

    try {
        liberarConversacion($pdo, (int) $_SESSION['conversation_id']);
    } catch (Throwable $e) {
        error_log('No fue posible liberar asesor al retirar consentimiento: ' . $e->getMessage());
    }
}

retirarConsentimientoWeb();

header('Location: espera_asesor.php');
exit;
