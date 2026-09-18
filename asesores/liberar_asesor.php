<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../consentimiento.php';

requirePostWithCsrf();
exigirConsentimientoWeb();
require __DIR__ . '/../conexion.php';
require __DIR__ . '/asignacion.php';

if (empty($_SESSION['conversation_id'])) {
    header('Location: ../index.php');
    exit;
}

try {
    liberarConversacion($pdo, (int) $_SESSION['conversation_id']);
} catch (Throwable $e) {
    error_log('Error liberando asesor: ' . $e->getMessage());
}

unset($_SESSION['conversation_id']);
header('Location: ../index.php');
exit;
