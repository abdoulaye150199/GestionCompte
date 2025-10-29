<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Compte;
use App\Http\Resources\CompteResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompteResourceBlocageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function les_dates_de_blocage_sont_affichees_uniquement_pour_les_comptes_epargne()
    {
        $compteEpargne = Compte::factory()->create([
            'type_compte' => 'epargne',
            'statut_compte' => 'actif',
            'date_debut_blocage' => now()->subDay(),
            'date_fin_blocage' => now()->addDays(10),
            'motif_blocage' => 'Test blocage'
        ]);

        $compteCheque = Compte::factory()->create([
            'type_compte' => 'cheque',
            'statut_compte' => 'actif',
            'date_debut_blocage' => now()->subDay(),
            'date_fin_blocage' => now()->addDays(10),
            'motif_blocage' => 'Test blocage'
        ]);

        $resourceEpargne = (new CompteResource($compteEpargne))->toArray(request());
        $resourceCheque = (new CompteResource($compteCheque))->toArray(request());

        $this->assertNotNull($resourceEpargne['dateDebutBlocage']);
        $this->assertNotNull($resourceEpargne['dateFinBlocage']);

        $this->assertNull($resourceCheque['dateDebutBlocage']);
        $this->assertNull($resourceCheque['dateFinBlocage']);
    }
}
