<?php

return [
    'default' => env('QUEUE_CONNECTION', env('QUEUE_DRIVER', 'sync')),
    'connections' => [
        'sync'    => ['driver' => 'sync'],
        'database'=> ['driver' => 'database', 'table' => 'jobs', 'queue' => 'default', 'connection' => null, 'after_commit' => true],
    ],
    'failed' => [
        'driver'   => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table'    => 'failed_jobs',
    ],
];
