<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Support\Str;
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
            'id' => (string) Str::uuid(),
            'client_id' => Client::inRandomOrder()->first()->id ?? Client::factory()->create()->id,
            'numero_compte' => 'CPT-' . $this->faker->unique()->numberBetween(100000, 999999),
            'type' => $this->faker->randomElement(['epargne', 'cheque']),
            'solde' => $this->faker->randomFloat(2, 0, 100000),
            'date_creation' => now(),
            'statut' => 'actif',
        ];
    }
}
