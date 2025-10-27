<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UnblockExpiredAccounts implements ShouldQueue
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
        Log::info('Démarrage du job de déblocage automatique des comptes expirés');

        // Récupérer tous les comptes bloqués dont la date de fin de blocage est échue
        $comptesExpires = Compte::where('statut', 'bloque')
            ->whereNotNull('date_fin_blocage')
            ->where('date_fin_blocage', '<=', Carbon::now())
            ->get();

        $comptesDebloques = 0;

        foreach ($comptesExpires as $compte) {
            try {
                // Mettre à jour les métadonnées avec les informations de déblocage automatique
                $metadonnees = $compte->metadonnees ?? [];
                $metadonnees['motifDeblocage'] = 'Déblocage automatique - Fin de période de blocage';
                $metadonnees['dateDeblocageAutomatique'] = Carbon::now()->toISOString();
                $metadonnees['debloqueAutomatiquement'] = true;

                // Débloquer le compte
                $compte->update([
                    'statut' => 'actif',
                    'date_fin_blocage' => null,
                    'metadonnees' => $metadonnees,
                ]);

                $comptesDebloques++;
                Log::info("Compte {$compte->numero_compte} débloqué automatiquement (période de blocage expirée)");

            } catch (\Exception $e) {
                Log::error("Erreur lors du déblocage automatique du compte {$compte->numero_compte}: " . $e->getMessage());
            }
        }

        Log::info("Job de déblocage automatique terminé: {$comptesDebloques} comptes débloqués");
    }
}
