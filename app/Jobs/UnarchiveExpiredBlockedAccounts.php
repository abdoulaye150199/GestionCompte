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

        $conn = DB::connection('archive');

        // Build a schema-resilient query: detect which columns exist and use them.
        $hasTypeCompte = $conn->getSchemaBuilder()->hasColumn('archived_comptes', 'type_compte');
        $hasStatutCompte = $conn->getSchemaBuilder()->hasColumn('archived_comptes', 'statut_compte');

        $query = $conn->table('archived_comptes')
            ->where(function ($q) use ($hasTypeCompte) {
                $q->where('type', 'epargne');
                if ($hasTypeCompte) {
                    $q->orWhere('type_compte', 'epargne');
                }
            })
            ->where(function ($q) use ($hasStatutCompte) {
                $q->where('statut', 'bloque');
                if ($hasStatutCompte) {
                    $q->orWhere('statut_compte', 'bloque');
                }
            });

        // Primary selection: rows with an explicit end date that is due
        $readyToRestore = (clone $query)
            ->whereNotNull('date_fin_blocage')
            ->where('date_fin_blocage', '<=', Carbon::now())
            ->get();

        $comptesDesarchives = 0;

        foreach ($readyToRestore as $compteArchive) {
            try {
                if (app()->environment('local') || config('queue.default') === 'sync') {
                    // run synchronously in local/testing
                    \App\Jobs\UnarchiveCompteJob::dispatchSync($compteArchive->id);
                    Log::info("UnarchiveCompteJob executed sync for archived compte {$compteArchive->numero_compte}");
                } else {
                    UnarchiveCompteJob::dispatch($compteArchive->id);
                    Log::info("Dispatched UnarchiveCompteJob for archived compte {$compteArchive->numero_compte}");
                }

                $comptesDesarchives++;
            } catch (\Exception $e) {
                Log::error("Erreur lors du dispatch du job de désarchivage pour {$compteArchive->numero_compte}: " . $e->getMessage());
            }
        }

        // Diagnostic: log how many archived rows have NULL date_fin_blocage (these won't be auto-restored)
        $withNullEnd = (clone $query)
            ->whereNull('date_fin_blocage')
            ->limit(10)
            ->get();

        if ($withNullEnd->count() > 0) {
            $ids = $withNullEnd->map(fn($r) => $r->numero_compte ?? $r->id)->toArray();
            Log::info('Archived comptes with NULL date_fin_blocage (these will not be auto-restored): ' . implode(',', $ids));
        }

        Log::info("Job de désarchivage terminé: {$comptesDesarchives} comptes désarchivés depuis Neon");
    }
}
