<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';

exigirConsentimientoWeb();
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Asistente Coomeva</title><link rel="stylesheet" href="css/style.css?v=20260915-5"></head>
<body class="wa-page"><main class="wa-app">
<header class="wa-topbar"><span class="wa-avatar">C</span><div><strong>Asistente Coomeva</strong><small>● En línea</small></div></header>
<section class="wa-chat">
    <div class="wa-date">HOY</div>
    <article class="wa-bubble wa-bot"><p>👋 No encontramos una afiliación asociada al documento ingresado.</p><p>Conoce nuestras opciones:</p><time>Ahora</time></article>
    <nav class="menu" aria-label="Opciones comerciales">
        <a href="planes.php">📋 1. Conocer planes</a>
        <a href="servicios.php">🏥 2. Conocer servicios</a>
        <a href="clinicas.php">🏨 3. Red de clínicas</a>
        <form action="asesor.php" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><button class="menu-action" type="submit">📝 4. Quiero afiliarme</button></form>
    </nav>
    <a class="wa-link-button" href="index.php">Nueva consulta</a>
</section>
</main></body>
</html>
