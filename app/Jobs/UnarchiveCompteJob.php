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

class UnarchiveCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $compteId;

    public function __construct(string $compteId)
    {
        $this->compteId = $compteId;
    }

    public function handle(): void
    {
        Log::info("UnarchiveCompteJob starting for {$this->compteId}");

        try {
            $archive = DB::connection('neon')->table('archived_comptes')->where('id', $this->compteId)->first();
            if (! $archive) {
                Log::warning("UnarchiveCompteJob: no archived record for {$this->compteId}");
                return;
            }

            // Restore into main DB
            $data = [
                'id' => $archive->id,
                'numero_compte' => $archive->numero_compte,
                'user_id' => $archive->user_id,
                'type_compte' => $archive->type,
                'solde' => $archive->solde,
                'devise' => $archive->devise,
                'statut_compte' => 'actif',
                'date_creation' => $archive->date_creation ?? Carbon::now(),
                'motif_blocage' => null,
                'date_debut_blocage' => null,
                'date_fin_blocage' => null,
                'metadonnees' => array_merge((array)($archive->metadonnees ?? []), ['restored_from_neon' => Carbon::now()->toISOString()]),
            ];

            // Insert using the model to ensure events & casting
            $compte = Compte::create($data);

            // Remove from neon
            DB::connection('neon')->table('archived_comptes')->where('id', $this->compteId)->delete();

            Log::info("UnarchiveCompteJob: restored compte {$this->compteId} from Neon");
        } catch (\Exception $e) {
            Log::error("UnarchiveCompteJob error for {$this->compteId}: " . $e->getMessage());
        }
    }
}
