<?php

return [
    'name' => env('APP_NAME', 'API_STOREDIMO'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost:8000'),

    'timezone' => env('APP_TIMEZONE', 'America/Bogota'),

    'locale' => 'en',

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',
];
