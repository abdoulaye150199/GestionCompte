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

class ArchiveCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $compteId;

    public function __construct(string $compteId)
    {
        $this->compteId = $compteId;
    }

    public function handle(): void
    {
        Log::info("ArchiveCompteJob starting for {$this->compteId}");

        $compte = Compte::withTrashed()->find($this->compteId);
        if (! $compte) {
            Log::warning("ArchiveCompteJob: compte {$this->compteId} not found");
            return;
        }

        try {
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
                'date_fin_blocage' => $compte->date_fin_blocage,
                'motif_blocage' => $compte->motif_blocage,
                'date_archivage' => Carbon::now(),
                'metadonnees' => $compte->metadonnees ?? null,
                'archived_at' => Carbon::now(),
            ];

            DB::connection('neon')->table('archived_comptes')->insert($archiveData);

            // remove from primary DB
            if (method_exists($compte, 'forceDelete')) {
                $compte->forceDelete();
            } else {
                $compte->delete();
            }

            Log::info("Compte {$this->compteId} archived to Neon via ArchiveCompteJob");
        } catch (\Exception $e) {
            Log::error("ArchiveCompteJob error for {$this->compteId}: " . $e->getMessage());
        }
    }
}
