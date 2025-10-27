<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveExpiredBlockedAccounts implements ShouldQueue
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
        Log::info('Démarrage du job d\'archivage des comptes bloqués expirés');

        // Récupérer tous les comptes bloqués dont la date de fin de blocage est échue
        $comptesExpires = Compte::where('statut', 'bloque')
            ->whereNotNull('date_fin_blocage')
            ->where('date_fin_blocage', '<=', Carbon::now())
            ->get();

        $comptesArchives = 0;

        foreach ($comptesExpires as $compte) {
            try {
                // Préparer les données d'archivage
                $archiveData = [
                    'id' => $compte->id,
                    'numero_compte' => $compte->numero_compte,
                    'user_id' => $compte->user_id,
                    'type' => $compte->type,
                    'solde' => $compte->solde,
                    'devise' => $compte->devise,
                    'statut' => $compte->statut,
                    'date_creation' => $compte->created_at,
                    'date_fin_blocage' => $compte->date_fin_blocage,
                    'date_archivage' => Carbon::now(),
                    'raison_archivage' => 'Blocage expiré',
                    'metadonnees' => $compte->metadonnees,
                    'archived_at' => Carbon::now(),
                ];

                // Insérer dans la base Neon
                DB::connection('neon')->table('archived_comptes')->insert($archiveData);

                // Supprimer définitivement de la base principale
                $compte->forceDelete();

                $comptesArchives++;
                Log::info("Compte {$compte->numero_compte} archivé dans Neon (blocage expiré)");

            } catch (\Exception $e) {
                Log::error("Erreur lors de l'archivage du compte {$compte->numero_compte}: " . $e->getMessage());
            }
        }

        Log::info("Job d'archivage terminé: {$comptesArchives} comptes archivés dans Neon");
    }
}
