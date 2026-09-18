<?php

return [

    'wompi' => [
        'events_secret'    => env('WOMPI_EVENTS_SECRET'),
    ],

    'app_web' => [
        'url'            => env('APP_WEB_URL'),
        'internal_token' => env('INTERNAL_TOKEN_SECRET'),
    ],

    'landing_key' => env('LANDING_API_KEY'),
];
