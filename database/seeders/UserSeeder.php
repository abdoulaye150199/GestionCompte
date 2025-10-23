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
    }
}
