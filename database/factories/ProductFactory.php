<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('KAYU-###'),
            'name' => fake()->randomElement(['Meranti', 'Jati', 'Mahoni']), 'wood_type' => fake()->randomElement(['Meranti', 'Jati', 'Mahoni']),
            'size' => '4x6 cm', 'unit' => 'batang', 'selling_price' => 125000,
            'stock' => 20, 'minimum_stock' => 5, 'is_active' => true,
        ];
    }
}
