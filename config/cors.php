<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['login',
        'logout',
        'sanctum/csrf-cookie',
        '/debug-csrf',
        'debug-auth',
        'api/*',
    ],

    'allowed_origins' => [
        env('CORS_ALLOWED_ORIGINS', 
        'http://localhost:3000'),
        'https://app.pludevelopers.co.ke',
    ],
 

    'allowed_origins_patterns' => [],
    'exposed_headers' => [],

    'allowed_headers' => ['*'],

    'max_age' => 0,

    'supports_credentials' => true,

];
