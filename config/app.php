<?php

return [
    'app' => [
        /*
         * Application name.
         */
        'name' => env('APP_NAME', 'RumahAsa'),

        'env' => env('APP_ENV', 'production'),
        'debug' => (bool) env('APP_DEBUG', false),
        'url' => env('APP_URL', 'http://localhost'),
        'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
        'locale' => env('APP_LOCALE', 'id'),
        'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
        'faker_locale' => env('APP_FAKER_LOCALE', 'id_ID'),

        'cipher' => 'AES-256-GCM',
        'key' => env('APP_KEY'),
        'previous_keys' => array_filter(
            array_map(fn ($key) => trim($key) !== '' ? trim($key) : null, preg_split('/\s*,\s*/', (string) env('APP_PREVIOUS_KEYS', '')))
        ),

        'maintenance' => [
            'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
            'store'  => env('APP_MAINTENANCE_STORE', 'database'),
        ],
    ],

    'rumahasa' => [
        'nik_hmac_key'          => env('NIK_HMAC_KEY', str_repeat('x', 32)),
        'foto_disk'             => env('FOTO_DISK', 'private'),
        'foto_max_size'         => (int) env('FOTO_MAX_SIZE', 5242880),
        'foto_max_per_komponen' => (int) env('FOTO_MAX_PER_KOMPONEN', 10),
        'login_max_attempts'    => (int) env('LOGIN_MAX_ATTEMPTS', 5),
        'login_lock_minutes'    => (int) env('LOGIN_LOCK_MINUTES', 15),
    ],
];
