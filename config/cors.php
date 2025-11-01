<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://gestioncompte-2.onrender.com',
        'http://localhost:3000',
        'http://127.0.0.1:8000',
        'https://gestioncompte-jmtc.onrender.com',
        'https://compte-api-transaction-abdoulaye-diallo.onrender.com',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];