<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Compte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\CompteController;

class DefaultFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function applique_le_filtre_par_defaut_types_epargne_et_cheque_et_exclut_bloque_ou_ferme()
    {
        // comptes créés
        $compteEpargneActif = Compte::factory()->create(['type_compte' => 'epargne', 'statut_compte' => 'actif']);
        $compteChequeActif = Compte::factory()->create(['type_compte' => 'cheque', 'statut_compte' => 'actif']);
        $compteEpargneBloque = Compte::factory()->create(['type_compte' => 'epargne', 'statut_compte' => 'bloque']);
        $compteCourant = Compte::factory()->create(['type_compte' => 'courant', 'statut_compte' => 'actif']);

        $controller = new CompteController();
        $query = Compte::query();
        $request = Request::create('/','GET');

        $paginator = $controller->applyQueryFilters($query, $request);
        $ids = collect($paginator->items())->pluck('id')->all();

        $this->assertContains($compteEpargneActif->id, $ids, 'Le compte épargne actif doit être présent');
        $this->assertContains($compteChequeActif->id, $ids, 'Le compte cheque actif doit être présent');
        $this->assertNotContains($compteEpargneBloque->id, $ids, 'Le compte épargne bloqué ne doit pas être présent');
        $this->assertNotContains($compteCourant->id, $ids, 'Le compte courant ne doit pas être présent');
    }
}
