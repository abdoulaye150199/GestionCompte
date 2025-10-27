<?php

namespace App\Traits;

trait ApiLinks
{
    /**
     * Generate HATEOAS links for a resource
     *
     * @param string $route Base route name
     * @param array $params Route parameters
     * @return array
     */
    protected function generateLinks(string $route, array $params = []): array
    {
        $links = [
            'self' => [
                'href' => route("api.{$route}.show", $params),
                'method' => 'GET'
            ]
        ];

        // Add other standard actions based on route
        if ($route === 'comptes') {
            $links['update'] = [
                'href' => route("api.{$route}.update", $params),
                'method' => 'PATCH'
            ];
            $links['delete'] = [
                'href' => route("api.{$route}.destroy", $params),
                'method' => 'DELETE'
            ];
            // Add related resources
            $links['titulaire'] = [
                'href' => route('api.users.show', ['id' => $params['user_id'] ?? null]),
                'method' => 'GET'
            ];
        }

        if ($route === 'users') {
            $links['update'] = [
                'href' => route("api.{$route}.update", $params),
                'method' => 'PATCH'
            ];
            $links['delete'] = [
                'href' => route("api.{$route}.destroy", $params),
                'method' => 'DELETE'
            ];
            // Add related resources
            $links['comptes'] = [
                'href' => route('api.comptes.index', ['user_id' => $params['id']]),
                'method' => 'GET'
            ];
        }

        return $links;
    }

    /**
     * Generate collection links (for paginated results)
     *
     * @param string $route Base route name
     * @param array $queryParams Current query parameters
     * @return array
     */
    protected function generateCollectionLinks(string $route, array $queryParams = []): array
    {
        $links = [
            'self' => [
                'href' => route("api.{$route}.index", $queryParams),
                'method' => 'GET'
            ],
            'create' => [
                'href' => route("api.{$route}.store"),
                'method' => 'POST'
            ]
        ];

        return $links;
    }
}