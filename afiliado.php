<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';

exigirConsentimientoWeb();
if (empty($_SESSION['afiliado_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Asistente Coomeva</title><link rel="stylesheet" href="css/style.css?v=20260915-5"></head>
<body class="wa-page"><main class="wa-app">
<header class="wa-topbar"><span class="wa-avatar">C</span><div><strong>Asistente Coomeva</strong><small>● En línea</small></div></header>
<section class="wa-chat">
    <div class="wa-date">HOY</div>
    <article class="wa-bubble wa-bot"><p>👋 ¡Bienvenido, <?= htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8') ?>!</p><p>¿Qué deseas consultar?</p><time>Ahora</time></article>
    <nav class="menu" aria-label="Opciones de afiliado">
        <a href="planes.php">📋 1. Mi plan</a>
        <a href="servicios.php">🏥 2. Mis servicios</a>
        <a href="citas.php">📅 3. Citas médicas</a>
        <a href="clinicas.php">🏨 4. Clínicas</a>
        <a href="especialistas.php">👨‍⚕️ 5. Especialistas</a>
        <form action="asesor.php" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><button class="menu-action" type="submit">👨‍💼 6. Hablar con un asesor</button></form>
    </nav>
    <form class="wa-decline" action="salir.php" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><button type="submit">Finalizar conversación</button></form>
</section>
</main></body>
</html>
