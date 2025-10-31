<?php

return [
    'paths' => ['api/*', 'v1/*', 'docs/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:8000')),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],
    'max_age' => 0,
    'supports_credentials' => false,
];