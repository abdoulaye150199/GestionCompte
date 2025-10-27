<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'nom' => $this->nom,
            'nci' => $this->nci,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'role' => $this->role_name,
            'login' => $this->login,
            'dateCreation' => $this->created_at,
            'derniereModification' => $this->updated_at,
        ];

        // Add HATEOAS links for REST Level 3 compliance
        $data['_links'] = [
            'self' => [
                'href' => route('users.show', ['user' => $this->id]),
                'method' => 'GET',
                'rel' => 'self'
            ],
            'update' => [
                'href' => route('users.update', ['user' => $this->id]),
                'method' => 'PATCH',
                'rel' => 'update'
            ],
            'delete' => [
                'href' => route('users.destroy', ['user' => $this->id]),
                'method' => 'DELETE',
                'rel' => 'delete'
            ],
            'collection' => [
                'href' => route('users.index'),
                'method' => 'GET',
                'rel' => 'collection'
            ]
        ];

        return $data;
    }
}
