<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'docs', 'docs/*'],

    'allowed_methods' => ['*'],

    // Use explicit origins when credentials are supported. Wildcard '*' is
    // incompatible with Access-Control-Allow-Credentials: true in browsers.
    // Allow all origins for local development / Swagger UI. Note: when using '*',
    // browsers will not allow credentials (cookies/Authorization with credentials).
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Authorization',
        'X-Auth-Token'
    ],

    'max_age' => 86400,

    // Disable credentials when using a wildcard origin.
    'supports_credentials' => false,

    // Cache preflight responses for 1 day
    'max_age' => 86400,

    // End of file

];
