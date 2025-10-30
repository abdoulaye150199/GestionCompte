<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$data = [
    'nom' => 'Test',
    'prenom' => 'User',
    'email' => 'layeprof221+ci_' . rand(1000,9999) . '@gmail.com',
    'telephone' => '+22178' . rand(1000000,9999999),
    'adresse' => 'Rue Debug',
    'nci' => 'NCI' . rand(1000000,9999999),
    'date_naissance' => '1990-01-01',
];

try {
    $user = User::createAccount($data);
    echo "Created user id: " . $user->id . PHP_EOL;
    echo "Email: " . ($user->email ?? '(none)') . "\n";
    echo "Telephone: " . ($user->telephone ?? '(none)') . "\n";
} catch (\Throwable $e) {
    echo "createAccount failed: " . $e->getMessage() . PHP_EOL;
}

