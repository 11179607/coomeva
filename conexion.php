<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

$dbConfig = appConfig()['db'] ?? [];
$host = (string) ($dbConfig['host'] ?? '');
$db = (string) ($dbConfig['name'] ?? '');
$user = (string) ($dbConfig['user'] ?? '');
$pass = (string) ($dbConfig['password'] ?? '');
$charset = (string) ($dbConfig['charset'] ?? 'utf8mb4');

if ($host === '' || $db === '' || $user === '') {
    http_response_code(500);
    error_log('Incomplete database configuration.');
    exit('Error de configuración.');
}

if (isProduction() && ($pass === '' || $user === 'root')) {
    http_response_code(500);
    error_log('Unsafe production database configuration.');
    exit('Error de configuración.');
}

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit('No fue posible conectar con la base de datos.');
}
