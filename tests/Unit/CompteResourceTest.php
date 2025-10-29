<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Resources\CompteResource;

class CompteResourceTest extends TestCase
{
    public function test_compte_resource_includes_links()
    {
        $compte = new \stdClass();
        $compte->id = 'abc-123';
        $compte->numero_compte = 'C001';
        $compte->client = (object)['id' => 'user-1'];
        $compte->solde = '1000';
        $compte->devise = 'FCFA';
        $compte->created_at = null;
        $compte->updated_at = null;

        $resource = new CompteResource($compte);
        $array = $resource->toArray(null);

        $this->assertArrayHasKey('_links', $array);
        $this->assertArrayHasKey('self', $array['_links']);
        $this->assertArrayHasKey('client', $array['_links']);
    }
}
