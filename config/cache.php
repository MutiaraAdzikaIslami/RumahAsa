<?php

return [
    'default'   => env('CACHE_STORE', env('CACHE_DRIVER', 'file')),
    'stores' => [
        'file'      => ['driver' => 'file', 'path' => storage_path('framework/cache/data')],
        'database'  => ['driver' => 'database', 'table' => env('CACHE_DB_TABLE', 'cache'), 'connection' => env('CACHE_DB_CONNECTION', null)],
        'array'     => ['driver' => 'array', 'serialize' => false],
    ],
    'prefix' => env('CACHE_PREFIX', 'rumahasa_'),
];
