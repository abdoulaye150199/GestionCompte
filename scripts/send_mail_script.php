<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/** @var \App\Services\EmailMessageService $svc */
$svc = $app->make(\App\Services\EmailMessageService::class);
$to = 'layeprof221@gmail.com';
$message = 'Test envoyé via script de debug: ' . date('c');
$ok = $svc->sendMessage($to, $message);
echo 'sendMessage returned: ' . ($ok ? 'true' : 'false') . PHP_EOL;
