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
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Citas médicas</title><link rel="stylesheet" href="css/style.css?v=20260915-5"></head>
<body class="wa-page"><main class="wa-app"><header class="wa-topbar"><span class="wa-avatar">C</span><div><strong>Asistente Coomeva</strong><small>● En línea</small></div></header><section class="wa-chat">
<div class="wa-date">HOY</div><article class="wa-bubble wa-bot"><p>📅 <strong>Citas médicas</strong></p><p>La solicitud, cancelación o reprogramación de citas se habilitará cuando exista una integración oficial y aprobada con los sistemas de Coomeva.</p><p>No compartas diagnósticos ni documentos médicos por este canal.</p><time>Ahora</time></article><a class="wa-link-button" href="afiliado.php">Volver al menú</a>
</section></main></body></html>
