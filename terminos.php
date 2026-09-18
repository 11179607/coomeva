<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/consentimiento.php';
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Términos y condiciones</title><link rel="stylesheet" href="css/style.css?v=20260915-5"></head>
<body class="wa-page"><main class="wa-app"><header class="wa-topbar"><span class="wa-avatar">C</span><div><strong>Asistente Coomeva</strong><small>Información legal</small></div></header><section class="wa-chat"><div class="wa-date">DOCUMENTO INFORMATIVO</div><article class="wa-bubble wa-bot"><h1>Términos y condiciones</h1><p>Este asistente permite consultar información y solicitar orientación. No reemplaza la atención médica, financiera ni contractual de Coomeva.</p><p>La información que se publique en este canal debe ser la versión oficial aprobada por Coomeva.</p><time>Versión <?= VERSION_TERMINOS ?? 'pendiente' ?></time></article><a class="wa-link-button" href="index.php">Volver</a></section></main></body>
</html>
