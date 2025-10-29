<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $base = [
            'numeroCompte' => $this->numero_compte,
            'titulaire' => isset($this->client) ? trim(($this->client->nom ?? '') . ' ' . ($this->client->prenom ?? '')) : ($this->titulaire_compte ?? null),
            'type' => $this->type_compte ?? $this->type,
            'solde' => $this->solde,
            'devise' => $this->devise ?? 'FCFA',
            'dateCreation' => optional($this->date_creation ?? $this->created_at)->toIso8601String(),
            'statut' => $this->statut_compte ?? $this->statut,
            'motifBlocage' => $this->motif_blocage ?? null,
            'dateBlocage' => optional($this->date_debut_blocage)->toIso8601String(),
            'dateDeblocagePrevue' => optional($this->date_fin_blocage)->toIso8601String(),
            'dateDeblocage' => optional($this->date_deblocage)->toIso8601String(),
            'dateFermeture' => optional($this->date_fermeture)->toIso8601String(),
            'metadata' => [
                'derniereModification' => optional($this->updated_at)->toIso8601String(),
                'version' => $this->version ?? 1,
            ],
        ];

        // Build HATEOAS links (non-breaking: adds `_links` alongside existing data)
        try {
            // Use the Hateoas trait helper if available
            $id = $this->id ?? null;
            $numero = $this->numero_compte ?? null;

            $links = [];
            $self = $numero ? url("/api/v1/comptes/numero/{$numero}") : ($id ? url("/api/v1/comptes/{$id}") : null);
            if ($self) {
                $links['self'] = $self;
            }
            if ($numero) {
                $links['transactions'] = url("/api/v1/comptes/{$numero}/transactions");
                $links['bloquer'] = url("/api/v1/comptes/numero/{$numero}/bloquer");
            }
            if ($id) {
                $links['archive'] = url("/api/v1/comptes/{$id}/archive");
                $links['debloquer'] = url("/api/v1/comptes/{$id}/debloquer");
            }
            if (isset($this->client)) {
                $links['client'] = url("/api/v1/users/{$this->client->id}");
            }

            $base['_links'] = $links;
        } catch (\Exception $e) {
            // Keep response safe — do not break if URL helpers fail
        }

        return $base;
    }
}
