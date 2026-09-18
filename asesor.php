<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';

requirePostWithCsrf();
exigirConsentimientoWeb();
require __DIR__ . '/conexion.php';
require __DIR__ . '/asesores/asignacion.php';

$asesor = null;
try {
    $assignment = asignarAsesor(
        $pdo,
        $_SESSION['telefono'] ?? 'WEB-' . session_id(),
        !empty($_SESSION['afiliado_id']) ? (int) $_SESSION['afiliado_id'] : null
    );
    $asesor = $assignment['asesor'] ?? null;
    if ($assignment) {
        $_SESSION['conversation_id'] = $assignment['conversation_id'];
    }
} catch (Throwable $e) {
    error_log('Error asignando asesor: ' . $e->getMessage());
}

$enlace = null;
if ($asesor) {
    $enlace = $asesor['enlace_whatsapp'];
    if (!$enlace && $asesor['telefono'] !== '') {
        $enlace = 'https://wa.me/' . preg_replace('/\D+/', '', $asesor['telefono']);
    }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Asesor Coomeva</title><link rel="stylesheet" href="css/style.css?v=20260915-5"></head>
<body class="wa-page"><main class="wa-app">
<header class="wa-topbar"><span class="wa-avatar">C</span><div><strong>Asistente Coomeva</strong><small>Atención personalizada</small></div></header>
<section class="wa-chat">
    <div class="wa-date">HOY</div>
    <?php if ($asesor && $enlace): ?>
        <article class="wa-bubble wa-bot"><p>👨‍💼 Te asignamos un asesor disponible.</p><p><strong><?= htmlspecialchars($asesor['nombre'], ENT_QUOTES, 'UTF-8') ?></strong> puede continuar tu atención.</p><time>Ahora</time></article>
        <a class="wa-link-button" href="<?= htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Contactar por WhatsApp</a>
        <article class="wa-bubble wa-bot"><p>Cuando termines la atención, finalízala para liberar la disponibilidad del asesor.</p><time>Ahora</time></article>
        <form class="wa-decline" action="asesores/liberar_asesor.php" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><button type="submit">Finalizar atención</button></form>
    <?php else: ?>
        <article class="wa-bubble wa-bot"><p>⏳ En este momento no hay asesores disponibles.</p><p>Intenta nuevamente en unos minutos.</p><time>Ahora</time></article>
    <?php endif; ?>
    <a class="wa-link-button" href="index.php">Volver al inicio</a>
</section>
</main></body>
</html>
