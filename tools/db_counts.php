<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo 'users: ' . DB::table('users')->count() . PHP_EOL;
echo 'comptes: ' . DB::table('comptes')->count() . PHP_EOL;
if (Schema::hasTable('account_transactions')) {
    echo 'account_transactions: ' . DB::table('account_transactions')->count() . PHP_EOL;
}
if (Schema::hasTable('transactions')) {
    echo 'transactions: ' . DB::table('transactions')->count() . PHP_EOL;
}
