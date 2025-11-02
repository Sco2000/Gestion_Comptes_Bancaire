<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero_transaction' => 'TD' . $this->faker->unique()->numberBetween(1000, 9999),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'type' => $this->faker->randomElement(['depot', 'retrait']),
            'montant' => $this->faker->randomFloat(2, 1000, 100000),
            'solde_apres' => $this->faker->randomFloat(2, 1000, 100000),
            'description' => $this->faker->optional()->sentence(),
            'client_id' => \App\Models\Client::factory(),
        ];
    }
}
