<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';
exigirConsentimientoWeb();

if (empty($_SESSION['afiliado_id'])) {
    header('Location: no_afiliado.php');
    exit;
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Especialistas Coomeva</title><link rel="stylesheet" href="css/style.css?v=20260915-5"></head>
<body class="wa-page"><main class="wa-app"><header class="wa-topbar"><span class="wa-avatar">C</span><div><strong>Asistente Coomeva</strong><small>● En línea</small></div></header><section class="wa-chat">
<div class="wa-date">HOY</div><article class="wa-bubble wa-bot"><p>👨‍⚕️ <strong>Especialistas</strong></p><p>La consulta de especialistas y disponibilidad se habilitará con información oficial y actualizada de Coomeva.</p><p>No compartas información médica sensible por este chat.</p><time>Ahora</time></article><a class="wa-link-button" href="afiliado.php">Volver al menú</a>
</section></main></body></html>
