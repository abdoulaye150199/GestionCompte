<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => \App\Models\User::factory(),
            'type' => $this->faker->randomElement(['epargne', 'cheque']),
            'solde' => $this->faker->randomFloat(2, 0, 1000000),
            'devise' => 'FCFA',
            'statut' => $this->faker->randomElement(['actif', 'bloque', 'ferme']),
            'metadonnees' => [
                'derniereModification' => now(),
                'version' => 1,
            ],
        ];
    }
}
