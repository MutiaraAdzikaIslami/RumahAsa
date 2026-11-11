<?php

return [
    'driver'          => env('SESSION_DRIVER', 'database'),
    'lifetime'        => env('SESSION_LIFETIME', 60),       // 1 jam (FR-02)
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', true),
    'encrypt'         => env('SESSION_ENCRYPT', true),      // encrypt NIK dll di sesi
    'files'           => storage_path('framework/sessions'),
    'connection'      => env('SESSION_CONNECTION'),
    'table'           => 'sessions',
    'store'           => env('SESSION_STORE'),
    'lottery'         => [2, 100],
    'cookie'          => env('SESSION_COOKIE', 'rumahasa_session'),
    'path'            => env('SESSION_PATH', '/'),
    'domain'          => env('SESSION_DOMAIN'),
    'secure'          => env('SESSION_SECURE_COOKIE'),
    'http_only'       => true,
    'same_site'       => 'lax',
    'block_inspector_ip' => env('SESSION_BLOCK_INSPECTOR_IP', true),
];
