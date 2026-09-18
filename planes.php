<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';
exigirConsentimientoWeb();
$returnPage = !empty($_SESSION['afiliado_id']) ? 'afiliado.php' : 'no_afiliado.php';
$title = !empty($_SESSION['afiliado_id']) ? 'Mi plan' : 'Planes Coomeva';
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $title ?></title><link rel="stylesheet" href="css/style.css?v=20260915-5"></head>
<body class="wa-page"><main class="wa-app"><header class="wa-topbar"><span class="wa-avatar">C</span><div><strong>Asistente Coomeva</strong><small>● En línea</small></div></header><section class="wa-chat">
<div class="wa-date">HOY</div><article class="wa-bubble wa-bot"><p>📋 <strong><?= $title ?></strong></p><p>Esta sección se conectará con la información oficial aprobada por Coomeva para mostrar planes, coberturas y condiciones actualizadas.</p><p>Para información personalizada, solicita la atención de un asesor desde el menú.</p><time>Ahora</time></article><a class="wa-link-button" href="<?= $returnPage ?>">Volver al menú</a>
</section></main></body></html>
