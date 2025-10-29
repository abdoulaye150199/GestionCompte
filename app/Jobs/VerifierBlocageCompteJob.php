<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerifierBlocageCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Handle the job.
     * Marks accounts as "bloqué" when their block start date has arrived and
     * archives related transactions.
     */
    public function handle()
    {
        $today = now()->startOfDay();

        $comptes = Compte::whereNotNull('date_debut_blocage')
            ->whereDate('date_debut_blocage', '<=', $today)
            ->where(function ($q) {
                // Normalize to 'bloque' spelling used across the codebase
                $q->whereNull('statut_compte')->orWhere('statut_compte', '!=', 'bloque');
            })
            ->get();

        foreach ($comptes as $compte) {
            try {
                $compte->statut_compte = 'bloque';
                $compte->save();

                // Archive related transactions if applicable
                if (method_exists($compte, 'transactions')) {
                    $compte->transactions()->update(['archived' => true]);
                }

                Log::info('Compte bloqué automatiquement', ['compte_id' => $compte->id]);
            } catch (\Exception $e) {
                Log::error('Erreur lors du blocage automatique du compte', ['compte_id' => $compte->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
