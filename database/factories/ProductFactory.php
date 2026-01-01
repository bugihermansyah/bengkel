<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Rack;
use App\Models\Supplier;
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
            'name' => $this->faker->word(),
            'sku' => $this->faker->unique()->ean13(),
            'category_id' => Category::factory(),
            'rack_id' => Rack::factory(),
            'supplier_id' => Supplier::factory(),
            'stock' => $this->faker->numberBetween(0, 100),
            'purchase_price' => $this->faker->randomFloat(2, 1000, 100000),
            'selling_price' => $this->faker->randomFloat(2, 1000, 100000),
            'image' => 'https://placehold.co/600x400',
            'status' => $this->faker->boolean(),
        ];
    }
}
