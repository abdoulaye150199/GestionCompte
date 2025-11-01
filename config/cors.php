<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'docs/*'],

    // Allow all methods
    'allowed_methods' => ['*'],

    // Explicit allowed origins: local frontend and production Render domain.
    // If you ever need credentials (cookies/Authorization with credentials),
    // add the exact origin here and set 'supports_credentials' => true.
    'allowed_origins' => ['http://localhost:8000', 'https://compte-api-transaction-abdoulaye-diallo.onrender.com'],

    'allowed_origins_patterns' => [],

    // Allow all headers
    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Authorization',
        'X-Auth-Token'
    ],

    // Cache preflight responses for 1 day
    'max_age' => 86400,

    // Do not support credentials for now (if you enable wildcard '*' you must set this to false)
    'supports_credentials' => true,

];