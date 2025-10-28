<?php

return [

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*', 'http://127.0.0.1:8000', 'http://localhost:8000', 'https://gestioncompte-api.onrender.com', 'https://compte-api-hjvy.onrender.com'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Origin',
        'Accept',
        'X-Auth-Token',
        'X-CSRF-TOKEN'
    ],

    'exposed_headers' => [
        'Authorization',
        'X-Auth-Token'
    ],

    'max_age' => 86400,

    'supports_credentials' => true

];
