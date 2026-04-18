<?php

namespace Database\Factories;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Merchant>
 */
class MerchantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => fake()->company(),
            'email'    => fake()->unique()->safeEmail(),
            'balance'  => fake()->numberBetween(0, 1_000_000),
            'currency' => 'HUF',
        ];
    }
}
