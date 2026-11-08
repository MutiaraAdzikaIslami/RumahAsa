<?php

use Illuminate\Session\Middleware\StartSession;

return [
    'logging' => [
        'deprecations' => [
            'channel' => env('LOG_DEPRECATIONS_CHANNEL'),
            'trace' => env('LOG_DEPRECATIONS_TRACE', false),
        ],
    ],
    'session' => [
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'same_site' => 'lax',
    ],
    'cookie' => [
        'encrypt' => env('SESSION_ENCRYPT', true),
        'samesite' => 'lax',
    ],
    'sanctum' => [
        'stateful' => ['*'],
    ],
    'cors' => [
        'paths' => ['api/*'],
        'allowed_methods' => ['*'],
        'allowed_origins' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => false,
    ],
];
