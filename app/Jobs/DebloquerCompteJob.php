<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DebloquerCompteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Handle the job.
     * Unblocks accounts whose block end date has passed and restores transactions.
     */
    public function handle()
    {
        $today = now()->startOfDay();

        $comptes = Compte::whereNotNull('date_fin_blocage')
            ->whereDate('date_fin_blocage', '<=', $today)
            ->where('statut_compte', 'bloque')
            ->get();

        foreach ($comptes as $compte) {
            try {
                $compte->statut_compte = 'actif';
                $compte->save();

                if (method_exists($compte, 'transactions')) {
                    $compte->transactions()->update(['archived' => false]);
                }

                Log::info('Compte débloqué automatiquement', ['compte_id' => $compte->id]);
            } catch (\Exception $e) {
                Log::error('Erreur lors du déblocage automatique du compte', ['compte_id' => $compte->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
