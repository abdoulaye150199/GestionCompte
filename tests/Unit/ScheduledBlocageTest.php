<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Compte;
use App\Jobs\VerifierBlocageCompteJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class ScheduledBlocageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_compte_planifie_a_une_date_future_n_est_pas_bloque_immediatement()
    {
        $tomorrow = Carbon::now()->addDay();
        $later = Carbon::now()->addDays(10);

        $compte = Compte::factory()->create([
            'type_compte' => 'epargne',
            'statut_compte' => 'actif',
            'date_debut_blocage' => $tomorrow->toDateString(),
            'date_fin_blocage' => $later->toDateString(),
            'motif_blocage' => 'Test planifié'
        ]);

        // Immediately after creation it should still be actif
        $this->assertEquals('actif', $compte->fresh()->statut_compte);

        // Run the Verifier job (should NOT block future-dated compte)
        (new VerifierBlocageCompteJob())->handle();

        $this->assertEquals('actif', $compte->fresh()->statut_compte);
    }

    /** @test */
    public function un_compte_avec_date_debut_passee_est_bloque_par_le_job()
    {
        $yesterday = Carbon::now()->subDay();
        $later = Carbon::now()->addDays(5);

        $compte = Compte::factory()->create([
            'type_compte' => 'epargne',
            'statut_compte' => 'actif',
            'date_debut_blocage' => $yesterday->toDateString(),
            'date_fin_blocage' => $later->toDateString(),
            'motif_blocage' => 'Test passé'
        ]);

        $this->assertEquals('actif', $compte->fresh()->statut_compte);

        (new VerifierBlocageCompteJob())->handle();

        $this->assertEquals('bloque', $compte->fresh()->statut_compte);
    }
}
