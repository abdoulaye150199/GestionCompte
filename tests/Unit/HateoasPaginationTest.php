<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\JsonResponse;

class HateoasPaginationTest extends TestCase
{
    public function test_paginated_response_includes_links()
    {
        // Create a tiny stub that uses the trait method signature
        $trait = new class {
            use \App\Traits\ApiResponseTrait;
        };

        $data = [['id' => 1]];
        $pagination = ['currentPage' => 1, 'itemsPerPage' => 1, 'totalPages' => 2];

        $response = $trait->paginatedResponse($data, $pagination, 'ok', 200);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $payload = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('_links', $payload);
        $this->assertArrayHasKey('self', $payload['_links']);
    }
}
