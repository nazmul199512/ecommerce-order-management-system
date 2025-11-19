<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vendor_id' => User::factory()->create(['role' => 'vendor']),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'base_price' => fake()->randomFloat(2, 10, 1000),
            'is_active' => true,
        ];
    }
}
