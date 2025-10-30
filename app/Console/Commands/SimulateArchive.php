<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Jobs\ArchiveCompteJob;
use App\Jobs\ArchiveExpiredBlockedAccounts;

class SimulateArchive extends Command
{
    protected $signature = 'simulate:archive {compteId?} {--expired : Run batch ArchiveExpiredBlockedAccounts}';
    protected $description = 'Simulate archiving a single compte or run the batch archive job';

    public function handle(): int
    {
        $compteId = $this->argument('compteId');
        if ($this->option('expired')) {
            $this->info('Running batch ArchiveExpiredBlockedAccounts job (synchronously)');
            try {
                $job = new ArchiveExpiredBlockedAccounts();
                $job->handle();
                $this->info('Batch archive job finished');
                return 0;
            } catch (\Throwable $e) {
                Log::error('SimulateArchive batch error: ' . $e->getMessage());
                $this->error('Batch archive job failed: ' . $e->getMessage());
                return 1;
            }
        }

        if (empty($compteId)) {
            $this->error('Please provide a compteId or use --expired to run the batch job');
            return 1;
        }

        $this->info("Simulating archive for compte {$compteId}");
        try {
            $job = new ArchiveCompteJob($compteId);
            $job->handle();
            $this->info('Archive job completed');
            return 0;
        } catch (\Throwable $e) {
            Log::error('SimulateArchive error: ' . $e->getMessage());
            $this->error('Archive job failed: ' . $e->getMessage());
            return 1;
        }
    }
}
