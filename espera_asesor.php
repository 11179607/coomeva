<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atención Coomeva</title>
    <link rel="stylesheet" href="css/style.css?v=20260915-5">
</head>
<body class="wa-page">
<main class="wa-app">
    <header class="wa-topbar">
        <span class="wa-avatar">C</span>
        <div><strong>Asistente Coomeva</strong><small>Atención al usuario</small></div>
    </header>
    <section class="wa-chat">
        <div class="wa-date">HOY</div>
        <article class="wa-bubble wa-bot">
            <p>Respetamos tu decisión. Para continuar con el asistente es necesario aceptar los términos y la autorización de tratamiento de datos.</p>
            <time>Ahora</time>
        </article>
        <article class="wa-bubble wa-bot">
            <p>Un asesor te contactará únicamente por un canal previamente autorizado. No ingreses tu cédula ni información médica en esta pantalla.</p>
            <time>Ahora</time>
        </article>
        <a class="wa-link-button" href="index.php">Volver a revisar la autorización</a>
    </section>
</main>
</body>
</html>
