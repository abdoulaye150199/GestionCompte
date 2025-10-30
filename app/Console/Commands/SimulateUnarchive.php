<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\UnarchiveCompteJob;
use App\Jobs\UnarchiveExpiredBlockedAccounts;

class SimulateUnarchive extends Command
{
    protected $signature = 'simulate:unarchive {compteId?} {--expired : Run batch UnarchiveExpiredBlockedAccounts}';
    protected $description = 'Simulate unarchiving a single compte or run the batch unarchive job';

    public function handle(): int
    {
        $compteId = $this->argument('compteId');
        if ($this->option('expired')) {
            $this->info('Running batch UnarchiveExpiredBlockedAccounts job (synchronously)');
            try {
                $job = new UnarchiveExpiredBlockedAccounts();
                $job->handle();
                $this->info('Batch unarchive job finished');
                return 0;
            } catch (\Throwable $e) {
                Log::error('SimulateUnarchive batch error: ' . $e->getMessage());
                $this->error('Batch unarchive job failed: ' . $e->getMessage());
                return 1;
            }
        }

        if (empty($compteId)) {
            $this->error('Please provide a compteId or use --expired to run the batch job');
            return 1;
        }

        $this->info("Simulating unarchive for compte {$compteId}");
        try {
            $job = new UnarchiveCompteJob($compteId);
            $job->handle();
            $this->info('Unarchive job completed');
            return 0;
        } catch (\Throwable $e) {
            Log::error('SimulateUnarchive error: ' . $e->getMessage());
            $this->error('Unarchive job failed: ' . $e->getMessage());
            return 1;
        }
    }
}
