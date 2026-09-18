<?php
declare(strict_types=1);

/*
 * Copy this file as config.local.php and set real values there.
 * config.local.php is deliberately ignored by Git and must stay outside
 * deployment artifacts when a platform provides environment variables.
 */
return [
    'app_env' => 'development', // Change to "production" on the real server.
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'coomeva',
        'user' => 'coomeva_app',
        'password' => 'REPLACE_WITH_A_STRONG_PASSWORD',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        // Use a unique random value of at least 32 characters in production.
        'rate_limit_pepper' => 'REPLACE_WITH_A_UNIQUE_RANDOM_SECRET_32_CHARS_MIN',
        // Set true on the HTTPS production domain.
        'session_cookie_secure' => false,
    ],
    'whatsapp' => [
        'verify_token' => 'REPLACE_WITH_A_LONG_RANDOM_VERIFY_TOKEN',
        'access_token' => '',
        'phone_number_id' => '',
        'app_secret' => '',
        // Configure the version confirmed for the Meta app before deployment.
        'graph_api_version' => 'vXX.X',
    ],
];
