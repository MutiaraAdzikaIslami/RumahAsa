<?php

return [
    'defaults' => [
        'passwords' => ['driver' => 'bcrypt', 'cost' => env('BCRYPT_ROUNDS', 12)],
        'provider' => 'users',
    ],
    'driver' => 'bcrypt',
    'bcrypt' => ['cost' => env('BCRYPT_ROUNDS', 12)],
    'argon' => [
        'memory' => 65536, 'threads' => 1, 'time' => 4,
    ],
];
