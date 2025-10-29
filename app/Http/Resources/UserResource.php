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

        // Add HATEOAS links for REST Level 3 compliance (safe fallback to urls)
        try {
            $id = $this->id;
            $data['_links'] = [
                'self' => [
                    'href' => url("/api/v1/users/{$id}"),
                    'method' => 'GET',
                    'rel' => 'self'
                ],
                'update' => [
                    'href' => url("/api/v1/users/{$id}"),
                    'method' => 'PATCH',
                    'rel' => 'update'
                ],
                'delete' => [
                    'href' => url("/api/v1/users/{$id}"),
                    'method' => 'DELETE',
                    'rel' => 'delete'
                ],
                'collection' => [
                    'href' => url("/api/v1/users"),
                    'method' => 'GET',
                    'rel' => 'collection'
                ]
            ];
        } catch (\Exception $e) {
            $data['_links'] = [];
        }

        return $data;
    }
}
