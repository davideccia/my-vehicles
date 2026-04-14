<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleRefuel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleRefuel>
 */
class VehicleRefuelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'date' => fake()->dateTimeBetween('-2 weeks', 'now')->format('Y-m-d'),
            'total_price' => fake()->randomFloat(2, 30, 120),
            'unit_price' => fake()->randomFloat(3, 1.5, 2.5),
            'liters' => fake()->randomFloat(2, 15, 60),
            'odometer' => fake()->numberBetween(10000, 200000),
        ];
    }
}
