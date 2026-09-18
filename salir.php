<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

requirePostWithCsrf();

if (!empty($_SESSION['conversation_id'])) {
    require __DIR__ . '/conexion.php';
    require __DIR__ . '/asesores/asignacion.php';

    try {
        liberarConversacion($pdo, (int) $_SESSION['conversation_id']);
    } catch (Throwable $e) {
        error_log('No fue posible liberar asesor al cerrar sesión: ' . $e->getMessage());
    }
}

session_unset();
if (session_id() !== '') {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $cookie['path'] ?? '/',
        'domain' => $cookie['domain'] ?? '',
        'secure' => (bool) ($cookie['secure'] ?? false),
        'httponly' => (bool) ($cookie['httponly'] ?? true),
        'samesite' => $cookie['samesite'] ?? 'Lax',
    ]);
}
session_destroy();
header('Location: index.php');
exit;
