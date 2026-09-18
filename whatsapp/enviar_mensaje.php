<?php
declare(strict_types=1);

require_once __DIR__ . '/../app.php';

function enviarWhatsApp(string $telefono, string $mensaje): bool
{
    $token = (string) appConfigValue('whatsapp', 'access_token', '');
    $phoneNumberId = (string) appConfigValue('whatsapp', 'phone_number_id', '');
    $graphApiVersion = (string) appConfigValue('whatsapp', 'graph_api_version', '');

    if ($token === '' || $phoneNumberId === '' || !preg_match('/^v\d+\.\d+$/', $graphApiVersion)) {
        error_log('WhatsApp no configurado correctamente.');
        return false;
    }

    $url = "https://graph.facebook.com/{$graphApiVersion}/{$phoneNumberId}/messages";

    $payload = [
        'messaging_product' => 'whatsapp',
        'to' => $telefono,
        'type' => 'text',
        'text' => ['body' => $mensaje],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($response === false) {
        error_log('WhatsApp API request failed: ' . curl_error($ch));
    }
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
}
