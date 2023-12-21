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
            'title' => $this->faker->sentence(2),
            'price' =>   $this->faker->numberBetween(1, 1000),
            'desc' => $this->faker->paragraph(3),
        ];
    }
}
