<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Prueba de asesores - Coomeva</title>
<link rel="stylesheet" href="css/style.css?v=20260915-5">
</head>
<body>
<main class="container">
<section class="card">
<h1>🧪 Prueba de asignación de asesores</h1>
<p>Abre esta página en dos navegadores o ventanas privadas diferentes y solicita un asesor en cada una.</p>
<ol>
<li>Primera sesión → debe recibir un asesor.</li>
<li>Segunda sesión → debe recibir el otro asesor disponible.</li>
<li>Tercera sesión → con ambos ocupados, debe indicar que no hay disponibilidad.</li>
<li>Libera una atención y prueba nuevamente.</li>
</ol>
<form action="asesor.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <button class="button" type="submit">Solicitar asesor</button>
</form>
<a class="secondary" href="index.php">Volver</a>
</section>
</main>
</body>
</html>
