<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver'  => 'local',
            'root'    => storage_path('app/private'),
            'serve'   => true,
            'throw'   => true,
            'report'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw'      => true,
            'report'     => false,
        ],

        'private' => [
            'driver'  => 'local',
            'root'    => storage_path('app/private'),
            'throw'   => true,
            'report'  => false,
            // Penting: tidak ada 'url' & 'visibility' = tidak bisa diakses publik
        ],
    ],
];
