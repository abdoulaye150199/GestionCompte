<?php

return [
    'client_uuids' => true,
    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'pgsql'),
        ],
    ],
    'cookie' => [
        'enabled' => false,
    ],
];