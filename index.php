<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';

$tieneConsentimiento = tieneConsentimientoWeb();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asistente Virtual Coomeva</title>
    <link rel="stylesheet" href="css/style.css?v=20260915-5">
</head>
<body class="wa-page">
<main class="wa-app">
    <header class="wa-topbar">
        <span class="wa-avatar">C</span>
        <div>
            <strong>Asistente Coomeva</strong>
            <small>● En línea</small>
        </div>
    </header>
    <section class="wa-chat">
        <div class="wa-date">HOY</div>
        <?php if (!$tieneConsentimiento): ?>
            <article class="wa-bubble wa-bot">
                <p>👋 ¡Hola! Soy el asistente virtual de Coomeva.</p>
                <p>Antes de continuar, necesitamos tu autorización para atenderte de forma segura.</p>
                <time>Ahora</time>
            </article>
            <form class="wa-consent" action="aceptar_consentimiento.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <label class="wa-check">
                    <input type="checkbox" name="acepta_terminos" value="si">
                    <span>Acepto los <a href="terminos.php" target="_blank" rel="noopener">términos y condiciones</a>.</span>
                </label>
                <label class="wa-check">
                    <input type="checkbox" name="acepta_datos" value="si">
                    <span>Autorizo el <a href="politica_privacidad.php" target="_blank" rel="noopener">tratamiento de mis datos personales</a> para validar mi afiliación y atender mi solicitud.</span>
                </label>
                <button class="wa-primary" type="submit">Aceptar y continuar</button>
            </form>
            <form class="wa-decline" action="rechazar_consentimiento.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">No acepto</button>
            </form>
        <?php else: ?>
            <article class="wa-bubble wa-bot">
                <p>Gracias por tu autorización. Para comenzar, escribe tu número de cédula.</p>
                <time>Ahora</time>
            </article>
            <form class="wa-compose" action="verificar_cedula.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <label class="sr-only" for="cedula">Número de cédula</label>
                <input id="cedula" name="cedula" inputmode="numeric" pattern="[0-9]{6,15}" minlength="6" maxlength="15" autocomplete="off" placeholder="Escribe tu número de cédula" required>
                <button class="wa-send" type="submit" aria-label="Enviar">➤</button>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
