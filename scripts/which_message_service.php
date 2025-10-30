<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$twilioEnv = getenv('TWILIO_ENABLED') ?: env('TWILIO_ENABLED');
$svc = $app->make(\App\Services\MessageServiceInterface::class);
echo 'TWILIO_ENABLED env: ' . var_export($twilioEnv, true) . PHP_EOL;
echo 'Resolved service class: ' . get_class($svc) . PHP_EOL;
if ($svc instanceof \App\Services\TwilioMessageService) {
    echo 'Twilio from: ' . (method_exists($svc, 'getFrom') ? $svc->getFrom() : '(no getter)') . PHP_EOL;
}
