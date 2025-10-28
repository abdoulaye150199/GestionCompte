<?php

namespace App\Jobs;

use App\Jobs\UnarchiveCompteJob;
use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnarchiveExpiredBlockedAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Démarrage du job de désarchivage des comptes bloqués expirés depuis Neon');

        // Récupérer tous les comptes archivés dans Neon qui étaient bloqués
        // et dont la date de fin de blocage est échue depuis plus de 30 jours
        $comptesArchives = DB::connection('neon')
            ->table('archived_comptes')
            ->where('statut', 'bloque')
            ->whereNotNull('date_fin_blocage')
            ->where('date_fin_blocage', '<=', Carbon::now()->subDays(30)) // 30 jours après expiration
            ->get();

        $comptesDesarchives = 0;

        foreach ($comptesArchives as $compteArchive) {
            try {
                // Dispatch a job to handle the restore so the work is centralized and retriable
                UnarchiveCompteJob::dispatch($compteArchive->id);
                $comptesDesarchives++;
                Log::info("Dispatched UnarchiveCompteJob for archived compte {$compteArchive->numero_compte}");
            } catch (\Exception $e) {
                Log::error("Erreur lors du dispatch du job de désarchivage pour {$compteArchive->numero_compte}: " . $e->getMessage());
            }
        }

        Log::info("Job de désarchivage terminé: {$comptesDesarchives} comptes désarchivés depuis Neon");
    }
}
