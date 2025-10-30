<?php

namespace App\Console\Commands;

use App\Jobs\ArchiveExpiredBlockedAccounts;
use App\Jobs\UnarchiveExpiredBlockedAccounts;
use App\Jobs\UnblockExpiredAccounts;
use Illuminate\Console\Command;

class RunAccountJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:run-jobs {--type=all : Type de job (unblock, archive, unarchive, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécuter les jobs de gestion des comptes (déblocage, archivage et désarchivage)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');

        $this->info('Démarrage des jobs de gestion des comptes...');

        switch ($type) {
            case 'unblock':
                $this->runUnblockJob();
                break;
            case 'archive':
                $this->runArchiveJob();
                break;
            case 'unarchive':
                $this->runUnarchiveJob();
                break;
            case 'all':
            default:
                $this->runUnblockJob();
                $this->runArchiveJob();
                $this->runUnarchiveJob();
                break;
        }

        $this->info('Jobs terminés avec succès!');
    }

    /**
     * Exécuter le job de déblocage
     */
    private function runUnblockJob()
    {
        $this->info('Exécution du job de déblocage automatique des comptes expirés...');
        UnblockExpiredAccounts::dispatch();
        $this->info('Job de déblocage dispatché avec succès.');
    }

    /**
     * Exécuter le job d'archivage
     */
    private function runArchiveJob()
    {
        $this->info('Exécution du job d\'archivage des comptes bloqués expirés...');
        ArchiveExpiredBlockedAccounts::dispatch();
        $this->info('Job d\'archivage dispatché avec succès.');
    }

    /**
     * Exécuter le job de désarchivage
     */
    private function runUnarchiveJob()
    {
        $this->info('Exécution du job de désarchivage des comptes bloqués expirés...');

        // In local or when the queue driver is "sync", run the job synchronously so
        // tests and small deployments without workers restore accounts immediately.
        try {
            if (app()->environment('local') || config('queue.default') === 'sync') {
                $this->info('Environnement local / queue sync détecté — exécution synchrone du désarchivage.');
                (new \App\Jobs\UnarchiveExpiredBlockedAccounts())->handle();
                $this->info('Désarchivage exécuté synchroniquement.');
            } else {
                UnarchiveExpiredBlockedAccounts::dispatch();
                $this->info('Job de désarchivage dispatché avec succès.');
            }
        } catch (\Throwable $e) {
            $this->error('Erreur lors de l\'exécution du désarchivage: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'exécution du désarchivage: ' . $e->getMessage());
        }
    }
}
