<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleServiceReminder;
use App\Models\VehicleServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleServiceReminder>
 */
class VehicleServiceReminderFactory extends Factory
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
            'every' => fake()->numberBetween(5000, 30000),
        ];
    }
}
