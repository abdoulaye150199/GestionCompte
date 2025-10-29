<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Compte;
use App\Models\Admin;
use Firebase\JWT\JWT;

class AccountAccessMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function generateJwtFor(User $user, $ttl = 3600)
    {
        $secret = env('JWT_SECRET');
        if (empty($secret)) {
            $secret = bin2hex(random_bytes(32));
            putenv('JWT_SECRET=' . $secret);
        }

        $now = time();
        $payload = [
            'iss' => config('app.url') ?: 'http://localhost',
            'iat' => $now,
            'exp' => $now + $ttl,
            'uuid' => (string) $user->id,
        ];

        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }

    public function test_client_cannot_access_other_users_compte()
    {
        // create owner and another user
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $compte = Compte::factory()->create([ 'user_id' => $owner->id ]);

        // generate JWT for the 'other' user
        $jwt = $this->generateJwtFor($other);

        $res = $this->withHeader('Authorization', 'Bearer ' . $jwt)
                    ->getJson('/api/v1/comptes/' . $compte->id);

        $res->assertStatus(403);
    }

    public function test_admin_can_access_any_compte()
    {
        $adminUser = User::factory()->create();
        // create an admin record linked to the user
        Admin::factory()->create(['user_id' => $adminUser->id]);

        $owner = User::factory()->create();
        $compte = Compte::factory()->create([ 'user_id' => $owner->id ]);

        $jwt = $this->generateJwtFor($adminUser);

        $res = $this->withHeader('Authorization', 'Bearer ' . $jwt)
                    ->getJson('/api/v1/comptes/' . $compte->id);

        $res->assertStatus(200);
    }
}
