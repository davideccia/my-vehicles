<?php

namespace Database\Factories;

use App\Models\VehicleServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleServiceType>
 */
class VehicleServiceTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'icon' => 'mdi-ab-testing',
            'label' => fake()->words(2, true),
        ];
    }
}
