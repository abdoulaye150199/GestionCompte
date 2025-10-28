<?php
namespace App\Traits;

use App\Jobs\ArchiveCompteJob;
use App\Jobs\UnarchiveCompteJob;
use App\Models\Compte;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

trait BlocageTrait
{
    protected function computeEndDate(int $duree, string $unite)
    {
        $now = now();
        switch ($unite) {
            case 'jours':
                return $now->copy()->addDays($duree);
            case 'mois':
                return $now->copy()->addMonths($duree);
            case 'annees':
                return $now->copy()->addYears($duree);
            default:
                return $now->copy()->addDays($duree);
        }
    }

    public function applyBlocage(Compte $compte, string $motif, int $duree, string $unite, ?string $initiator = null)
    {
        // Only savings accounts may be blocked
        $type = $compte->type_compte ?? $compte->type ?? null;
        if ($type !== 'epargne') {
            throw new \App\Exceptions\ValidationException(['type_compte' => ['Seul les comptes de type "epargne" peuvent être bloqués.']]);
        }

        if (($compte->statut_compte ?? $compte->statut) !== 'actif') {
            throw new \App\Exceptions\CompteDejaBloqueException('Le compte n\'est pas actif et ne peut être bloqué.');
        }

        $start = now();
        $end = $this->computeEndDate($duree, $unite);

        $compte->motif_blocage = $motif;
        $compte->date_debut_blocage = $start;
        $compte->date_fin_blocage = $end;
        $compte->statut_compte = 'bloque';
        $compte->save();

        Log::channel('comptes')->info('Compte bloqué', ['compte_id' => $compte->id, 'motif' => $motif, 'duree' => $duree, 'unite' => $unite, 'initiator' => $initiator]);

        // Schedule archival at block start (immediate if start is now or in the past)
        try {
            $now = now();
            if ($start->greaterThan($now)) {
                ArchiveCompteJob::dispatch($compte->id)->delay($start);
            } else {
                ArchiveCompteJob::dispatch($compte->id);
            }

            // Always schedule unarchive at end of block (restore to Railway when date_fin_blocage is reached)
            if ($end->greaterThan($now)) {
                UnarchiveCompteJob::dispatch($compte->id)->delay($end);
            } else {
                UnarchiveCompteJob::dispatch($compte->id);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du scheduling des jobs de blocage pour compte ' . $compte->id . ': ' . $e->getMessage());
        }

        return $compte->fresh();
    }

    public function applyDeblocage(Compte $compte, string $motif, ?string $initiator = null)
    {
        if (($compte->statut_compte ?? $compte->statut) !== 'bloque') {
            throw new \App\Exceptions\CompteNotBloqueException('Le compte n\'est pas bloqué.');
        }

        $compte->statut_compte = 'actif';
        $compte->date_deblocage = now();
        $compte->save();

        Log::channel('comptes')->info('Compte débloqué', ['compte_id' => $compte->id, 'motif' => $motif, 'initiator' => $initiator]);

        return $compte->fresh();
    }
}
