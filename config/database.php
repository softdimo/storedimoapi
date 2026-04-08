<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'softdimo.com'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'softdimo_storedimo'),
            'username' => env('DB_USERNAME', 'softdimo_storedimo'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'timezone' => '-05:00', // <--- Esto fuerza a MySQL a trabajar en hora Colombia
        ],

        'tenant' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'softdimo.com'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'softdimo_storedimo'),
            'username' => env('DB_USERNAME', 'softdimo_storedimo'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'timezone' => '-05:00', // <--- Esto fuerza a MySQL a trabajar en hora Colombia
        ],
    ],
];
