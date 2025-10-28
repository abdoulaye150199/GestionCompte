<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Illuminate\Support\Str;

class PassportSeeder extends Seeder
{
    public function run()
    {
        // Créer un client d'accès personnel si aucun n'existe
        if (!Client::where('personal_access_client', true)->exists()) {
            Client::create([
                'id' => (string) Str::uuid(),
                'name' => 'Personal Access Client',
                'secret' => hash('sha256', 'personal-access-secret'),
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'personal_access_client' => true,
                'password_client' => false,
                'revoked' => false,
            ]);
        }

        // Créer un client Password Grant si aucun n'existe
        if (!Client::where('password_client', true)->exists()) {
            Client::create([
                'id' => (string) Str::uuid(),
                'name' => 'Password Grant Client',
                'secret' => hash('sha256', 'password-grant-secret'),
                'provider' => 'users',
                'redirect' => 'http://localhost',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
            ]);
        }
    }
}