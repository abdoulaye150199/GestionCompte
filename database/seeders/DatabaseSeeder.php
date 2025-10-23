<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer 10 clients avec chacun 1 à 3 comptes bancaires
        \App\Models\Client::factory(10)
            ->has(
                \App\Models\BankAccount::factory()
                    ->count(fake()->numberBetween(1, 3))
            )
            ->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
