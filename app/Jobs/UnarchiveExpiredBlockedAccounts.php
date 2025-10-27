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
                // Créer un nouveau compte dans la base principale avec les données archivées
                $nouveauCompte = Compte::create([
                    'id' => $compteArchive->id,
                    'numero_compte' => $compteArchive->numero_compte,
                    'user_id' => $compteArchive->user_id,
                    'type' => $compteArchive->type,
                    'solde' => $compteArchive->solde,
                    'devise' => $compteArchive->devise,
                    'statut' => 'actif', // Remettre en actif
                    'metadonnees' => array_merge($compteArchive->metadonnees ?? [], [
                        'dateDesarchivage' => Carbon::now()->toISOString(),
                        'raisonDesarchivage' => 'Fin de période de blocage (depuis archivage)',
                        'restaureDepuisNeon' => true,
                    ]),
                ]);

                // Supprimer de la base Neon
                DB::connection('neon')
                    ->table('archived_comptes')
                    ->where('id', $compteArchive->id)
                    ->delete();

                $comptesDesarchives++;
                Log::info("Compte {$compteArchive->numero_compte} désarchivé depuis Neon (fin de blocage)");

            } catch (\Exception $e) {
                Log::error("Erreur lors du désarchivage du compte {$compteArchive->numero_compte}: " . $e->getMessage());
            }
        }

        Log::info("Job de désarchivage terminé: {$comptesDesarchives} comptes désarchivés depuis Neon");
    }
}
