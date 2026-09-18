<?php
declare(strict_types=1);

function appConfig(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $configFile = __DIR__ . '/config.local.php';
    $config = is_file($configFile) ? require $configFile : [];
    if (!is_array($config)) {
        http_response_code(500);
        error_log('Invalid local configuration.');
        exit('Error de configuración.');
    }

    $environmentMap = [
        'APP_ENV' => ['app_env'],
        'DB_HOST' => ['db', 'host'],
        'DB_NAME' => ['db', 'name'],
        'DB_USER' => ['db', 'user'],
        'DB_PASSWORD' => ['db', 'password'],
        'DB_CHARSET' => ['db', 'charset'],
        'RATE_LIMIT_PEPPER' => ['security', 'rate_limit_pepper'],
        'SESSION_COOKIE_SECURE' => ['security', 'session_cookie_secure'],
        'WHATSAPP_VERIFY_TOKEN' => ['whatsapp', 'verify_token'],
        'WHATSAPP_ACCESS_TOKEN' => ['whatsapp', 'access_token'],
        'WHATSAPP_PHONE_NUMBER_ID' => ['whatsapp', 'phone_number_id'],
        'WHATSAPP_APP_SECRET' => ['whatsapp', 'app_secret'],
        'WHATSAPP_GRAPH_API_VERSION' => ['whatsapp', 'graph_api_version'],
    ];

    foreach ($environmentMap as $environmentVariable => $path) {
        $value = getenv($environmentVariable);
        if ($value === false || $value === '') {
            continue;
        }

        if (count($path) === 1) {
            $config[$path[0]] = $value;
            continue;
        }

        $config[$path[0]] ??= [];
        $config[$path[0]][$path[1]] = $environmentVariable === 'SESSION_COOKIE_SECURE'
            ? filter_var($value, FILTER_VALIDATE_BOOL)
            : $value;
    }

    return $config;
}

function appConfigValue(string $section, string $key, mixed $default = null): mixed
{
    $config = appConfig();
    return $config[$section][$key] ?? $default;
}

function isProduction(): bool
{
    return (appConfig()['app_env'] ?? 'development') === 'production';
}

function isHttpsRequest(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) === '443');
}

function sendSecurityHeaders(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; style-src 'self'; img-src 'self' data:");

    if (isHttpsRequest()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    session_name('coomeva_session');
    $secureCookie = (bool) appConfigValue('security', 'session_cookie_secure', isHttpsRequest());
    if (isProduction() && !$secureCookie) {
        http_response_code(500);
        error_log('Production sessions require Secure cookies over HTTPS.');
        exit('Error de configuración.');
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrfToken(): string
{
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function requirePostWithCsrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Método no permitido.');
    }

    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) || !hash_equals(csrfToken(), $submittedToken)) {
        http_response_code(403);
        exit('Solicitud no válida. Recarga la página e inténtalo de nuevo.');
    }
}

function clientIpAddress(): string
{
    // Do not trust X-Forwarded-For unless the production proxy is explicitly trusted.
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
}

function identificationRateLimit(PDO $pdo): bool
{
    return consumeRateLimit($pdo, 'identification', clientIpAddress());
}

function whatsappIdentificationRateLimit(PDO $pdo, string $telefono): bool
{
    return consumeRateLimit($pdo, 'whatsapp_identification', $telefono);
}

function consumeRateLimit(PDO $pdo, string $scope, string $source): bool
{
    $pepper = (string) appConfigValue('security', 'rate_limit_pepper', '');
    if (isProduction() && strlen($pepper) < 32) {
        error_log('A production rate_limit_pepper of at least 32 characters is required.');
        return false;
    }

    $identifier = hash('sha256', $scope . '|' . $source . '|' . $pepper);
    $pdo->beginTransaction();

    try {
        $select = $pdo->prepare(
            'SELECT attempts, window_started_at FROM rate_limits
             WHERE scope = :scope AND identifier_hash = :identifier FOR UPDATE'
        );
        $select->execute(['scope' => $scope, 'identifier' => $identifier]);
        $record = $select->fetch();
        $now = new DateTimeImmutable('now');

        if (!$record) {
            $insert = $pdo->prepare(
                'INSERT INTO rate_limits (scope, identifier_hash, window_started_at, attempts)
                 VALUES (:scope, :identifier, NOW(), 1)'
            );
            $insert->execute(['scope' => $scope, 'identifier' => $identifier]);
            $allowed = true;
        } elseif ((new DateTimeImmutable($record['window_started_at']))->modify('+15 minutes') <= $now) {
            $reset = $pdo->prepare(
                'UPDATE rate_limits SET window_started_at = NOW(), attempts = 1
                 WHERE scope = :scope AND identifier_hash = :identifier'
            );
            $reset->execute(['scope' => $scope, 'identifier' => $identifier]);
            $allowed = true;
        } else {
            $attempts = (int) $record['attempts'] + 1;
            $update = $pdo->prepare(
                'UPDATE rate_limits SET attempts = :attempts
                 WHERE scope = :scope AND identifier_hash = :identifier'
            );
            $update->execute(['attempts' => $attempts, 'scope' => $scope, 'identifier' => $identifier]);
            $allowed = $attempts <= 5;
        }

        $pdo->commit();
        return $allowed;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Could not apply identification rate limit: ' . $e->getMessage());
        // Fail closed in production, where protecting personal data takes priority.
        return !isProduction();
    }
}
