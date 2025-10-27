<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
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
            'user_id' => \App\Models\User::factory(),
            'adresse' => $this->faker->optional()->address(),
            'date_naissance' => $this->faker->optional()->date(),
        ];
    }
}
