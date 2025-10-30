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

        // Récupérer tous les comptes épargne bloqués dont la date de début de blocage est échue
        // (Archivage doit se produire au début du blocage). Use explicit column checks
        // to remain compatible with deployments that may have either 'type_compte'
        ///'statut_compte' or legacy 'type'/'statut' columns.
        $query = Compte::query();

        // Type check (prefer type_compte)
        if (\Illuminate\Support\Facades\Schema::hasColumn('comptes', 'type_compte')) {
            $query->where('type_compte', 'epargne');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('comptes', 'type')) {
            $query->where('type', 'epargne');
        }

        // Statut check (prefer statut_compte)
        if (\Illuminate\Support\Facades\Schema::hasColumn('comptes', 'statut_compte')) {
            $query->where('statut_compte', 'bloque');
        } elseif (\Illuminate\Support\Facades\Schema::hasColumn('comptes', 'statut')) {
            $query->where('statut', 'bloque');
        }

        $comptesAArchiver = $query
            ->whereNotNull('date_debut_blocage')
            ->where('date_debut_blocage', '<=', Carbon::now())
            ->get();

        $comptesArchives = 0;

        foreach ($comptesAArchiver as $compte) {
            try {
                // Préparer les données d'archivage
                $archiveData = [
                    'id' => $compte->id,
                    'numero_compte' => $compte->numero_compte,
                    'user_id' => $compte->user_id,
                    'type' => $compte->type_compte ?? $compte->type,
                    'solde' => $compte->solde,
                    'devise' => $compte->devise,
                    'statut' => $compte->statut_compte ?? $compte->statut,
                    'date_creation' => $compte->date_creation ?? $compte->created_at,
                    'date_debut_blocage' => $compte->date_debut_blocage,
                    'date_fin_blocage' => $compte->date_fin_blocage ?? null,
                    'motif_blocage' => $compte->motif_blocage ?? null,
                    'date_archivage' => Carbon::now(),
                    'raison_archivage' => 'Archivage au début du blocage',
                    'metadonnees' => $compte->metadonnees ?? null,
                    'archived_at' => Carbon::now(),
                ];

                // Insérer dans la base d'archive (Neon / archive DB)
                DB::connection('archive')->table('archived_comptes')->insert($archiveData);

                // If the original compte had a planned end date, ensure an Unarchive job is scheduled
                // so the compte will be restored when the block period ends.
                if (! empty($compte->date_fin_blocage)) {
                    try {
                        if (now()->lt($compte->date_fin_blocage)) {
                            UnarchiveCompteJob::dispatch($compte->id)->delay($compte->date_fin_blocage);
                        } else {
                            UnarchiveCompteJob::dispatch($compte->id);
                        }
                    } catch (\Exception $e) {
                        Log::error('Erreur lors du scheduling du UnarchiveCompteJob après archivage pour ' . $compte->id . ': ' . $e->getMessage());
                    }
                }
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
