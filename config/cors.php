<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'docs/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://127.0.0.1:8000',
        'http://localhost:8000',
        'https://compte-api-transaction-abdoulaye-diallo.onrender.com',
        'https://gestioncompte-2.onrender.com'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Authorization',
        'X-Auth-Token'
    ],

    'max_age' => 86400,

    'supports_credentials' => true,
];
