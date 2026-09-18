<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';
exigirConsentimientoWeb();
$returnPage = !empty($_SESSION['afiliado_id']) ? 'afiliado.php' : 'no_afiliado.php';
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Clínicas Coomeva</title><link rel="stylesheet" href="css/style.css?v=20260915-5"></head>
<body class="wa-page"><main class="wa-app"><header class="wa-topbar"><span class="wa-avatar">C</span><div><strong>Asistente Coomeva</strong><small>● En línea</small></div></header><section class="wa-chat">
<div class="wa-date">HOY</div><article class="wa-bubble wa-bot"><p>🏨 <strong>Clínicas y centros médicos</strong></p><p>La red se mostrará cuando Coomeva entregue y autorice la fuente oficial para este canal.</p><p>Para una necesidad urgente, usa los canales de atención definidos por tu plan o las líneas de emergencia correspondientes.</p><time>Ahora</time></article><a class="wa-link-button" href="<?= $returnPage ?>">Volver al menú</a>
</section></main></body></html>
