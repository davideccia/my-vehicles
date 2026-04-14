<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleService;
use App\Models\VehicleServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleService>
 */
class VehicleServiceFactory extends Factory
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
            'vehicle_service_type_id' => VehicleServiceType::factory(),
            'date' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'total_paid' => fake()->randomFloat(2, 30, 500),
            'odometer' => fake()->numberBetween(10000, 200000),
            'location' => fake()->optional()->city(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
