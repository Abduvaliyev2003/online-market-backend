<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
            'category_id' => rand(1, 6),
            'title' => $this->faker->sentence(4),
            'price' =>   $this->faker->randomFloat(2, 1, 100),
            'desc' => $this->faker->paragraph(3),
        ];
    }
}
