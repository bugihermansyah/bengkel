<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'plate_number' => $this->faker->bothify('B #### ???'),
            'brand' => $this->faker->word(),
            'type' => $this->faker->word(),
            'year' => $this->faker->year(),
            'status' => $this->faker->boolean(),
        ];
    }
}
