<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'docs', 'docs/*'],

    'allowed_methods' => ['*'],

    // Use explicit origins when credentials are supported. Wildcard '*' is
    // incompatible with Access-Control-Allow-Credentials: true in browsers.
    'allowed_origins' => [
        'https://gestioncompte-api.onrender.com.abdoulaye.diallo',
        'https://compte-api-hjvy.onrender.com',
        'http://127.0.0.1:8000',
        'http://localhost:8000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Authorization',
        'X-Auth-Token'
    ],

    'max_age' => 86400,

    // Keep credentials support if you rely on cookie-based auth (Sanctum).
    'supports_credentials' => true,

    // Cache preflight responses for 1 day
    'max_age' => 86400,

    // End of file

];
