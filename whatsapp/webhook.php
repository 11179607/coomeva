<?php
declare(strict_types=1);

require_once __DIR__ . '/../app.php';

$verifyToken = (string) appConfigValue('whatsapp', 'verify_token', '');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';

    if ($verifyToken !== '' && $mode === 'subscribe' && hash_equals($verifyToken, $token)) {
        http_response_code(200);
        echo $challenge;
        exit;
    }

    http_response_code(403);
    exit('Forbidden');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: GET, POST');
    exit('Method not allowed');
}

$rawPayload = file_get_contents('php://input');
$appSecret = (string) appConfigValue('whatsapp', 'app_secret', '');
$signature = (string) ($_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '');
$expectedSignature = $appSecret === '' ? '' : 'sha256=' . hash_hmac('sha256', $rawPayload, $appSecret);

if ($expectedSignature === '' || !hash_equals($expectedSignature, $signature)) {
    error_log('Rejected WhatsApp webhook with an invalid signature.');
    http_response_code(401);
    exit('Unauthorized');
}

try {
    $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    $payload = null;
}

if (!is_array($payload)) {
    http_response_code(400);
    exit('Invalid JSON');
}

require __DIR__ . '/procesar_mensaje.php';
try {
    procesarMensajeWhatsApp($payload);
} catch (Throwable $e) {
    error_log('WhatsApp webhook processing failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Temporary error');
}

http_response_code(200);
echo 'EVENT_RECEIVED';
