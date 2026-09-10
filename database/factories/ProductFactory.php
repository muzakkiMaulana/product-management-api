<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'price' => (string) fake()->numberBetween(1, 1000000),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['Clothes', 'Electronics', 'Furniture']),
            'images' => ['https://example.com/images/product.jpg'],
            'created_by_id' => User::factory(),
            'created_by' => fn (array $attributes) => User::findOrFail($attributes['created_by_id'])->username,
        ];
    }
}
