<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Compte;
use Illuminate\Support\Facades\DB;
use App\Jobs\ArchiveExpiredBlockedAccounts;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

// Find a candidate compte: epargne, actif, not archived
$query = DB::table('comptes')
    ->where('type_compte', 'epargne')
    ->where('statut_compte', 'actif');

if (Schema::hasColumn('comptes', 'archived')) {
    $query->whereRaw('archived = false');
}

$row = $query->first();

if (! $row) {
    echo "No suitable compte found to test archive.\n";
    exit(1);
}

$compte = Compte::find($row->id);

if (! $compte) {
    echo "Could not load Compte model for id {$row->id}\n";
    exit(1);
}
$now = Carbon::now();
$compte->statut_compte = 'bloque';
$compte->date_debut_blocage = $now;
$compte->date_fin_blocage = $now->copy()->addMinutes(2);
$compte->motif_blocage = 'Test 2 minutes - suspicion de fraude';
$compte->save();

echo "Compte {$compte->numero_compte} ({$compte->id}) marked as blocked from {$compte->date_debut_blocage} to {$compte->date_fin_blocage}\n";

// Run the batch archive job synchronously
$job = new ArchiveExpiredBlockedAccounts();
$job->handle();

echo "Archive job executed. Check storage/logs/laravel.log for Neon insertion logs.\n";

// Print last 80 lines of log to show Neon activity
$logTail = shell_exec('tail -n 80 storage/logs/laravel.log 2>&1');
echo "--- recent logs ---\n" . $logTail . "\n";

echo "Now wait ~2 minutes and run: php artisan simulate:unarchive --expired to restore the compte (or run scripts/test_unarchive_flow.php if preferred).\n";
