<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
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
            'login' => $this->faker->unique()->userName(),
            'password' => bcrypt('password'),
        ];
    }

    public function admin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'login' => $this->faker->unique()->userName(),
                'password' => bcrypt('admin123'),
            ];
        });
    }
}
