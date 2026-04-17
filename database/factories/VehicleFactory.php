<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plate_number' => strtoupper(fake()->bothify('??###??')),
            'brand' => fake()->randomElement(['Alfa Romeo']),
            'model' => fake()->randomElement(['Tonale', 'Stelvio', 'Giulia', 'Giulietta', '146', '147', '156']),
            'year' => fake()->numberBetween(2000, 2025),
            'purchase_date' => fake()->optional()->dateTimeBetween('-25 years')?->format('Y-m-d'),
            'color' => fake()->hexColor(),
        ];
    }
}
