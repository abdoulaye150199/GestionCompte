<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur admin avec son profil
        $adminUser = \App\Models\User::factory()->create([
            'login' => 'admin',
            'password' => bcrypt('admin123'),
            'type' => 'admin',
        ]);

        \App\Models\Admin::factory()->create([
            'user_id' => $adminUser->id,
            'nom' => 'Administrateur Système',
            'nci' => '0000000000',
            'email' => 'admin@banque.com',
            'telephone' => '+221000000000',
            'adresse' => 'Siège de la Banque, Dakar',
        ]);

        // Créer des utilisateurs clients avec leurs profils
        \App\Models\Client::factory(9)->create();

        // Créer un client OAuth pour l'authentification par mot de passe
        if (!\Laravel\Passport\Client::where('password_client', true)->exists()) {
            \Laravel\Passport\Client::create([
                'id' => '4fb47d8e-7df3-469b-b130-2e4510526fc4',
                'name' => 'Testing Password Grant Client',
                'secret' => '$2y$10$f8RQ3TyIab7sxXfZeDFWtPC1EWJ8gzEi9tiU8aji',
                'redirect' => 'http://localhost',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
                'provider' => 'users',
            ]);
        }
    }
}
