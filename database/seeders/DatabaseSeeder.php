<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleServiceType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $tagliando = VehicleServiceType::create([
            'icon' => 'mdi-car-wrench',
            'label' => 'Tagliando',
        ]);

        // Veicolo 1 — Fiat Punto: odometro 50.000 → 58.700 (delta ~8.700 km < 10.000) → reminder NON scattato
        $fiatPunto = Vehicle::create([
            'plate_number' => 'AA123BB',
            'brand' => 'Fiat',
            'model' => 'Punto',
            'year' => 2019,
            'purchase_date' => '2019-06-01',
        ]);

        $fiatPunto->vehicleServiceReminders()->create([
            'vehicle_service_type_id' => $tagliando->id,
            'every' => 10000,
        ]);

        $fiatPunto->vehicleRefuels()->createMany([
            ['date' => '2025-01-10', 'odometer' => 50000, 'liters' => 40.0, 'unit_price' => 1.82, 'total_price' => 72.80],
            ['date' => '2025-02-03', 'odometer' => 51200, 'liters' => 38.5, 'unit_price' => 1.79, 'total_price' => 68.92],
            ['date' => '2025-02-28', 'odometer' => 52500, 'liters' => 42.0, 'unit_price' => 1.85, 'total_price' => 77.70],
            ['date' => '2025-03-22', 'odometer' => 53800, 'liters' => 39.0, 'unit_price' => 1.80, 'total_price' => 70.20],
            ['date' => '2025-04-14', 'odometer' => 55000, 'liters' => 41.5, 'unit_price' => 1.83, 'total_price' => 75.95],
            ['date' => '2025-05-07', 'odometer' => 56300, 'liters' => 38.0, 'unit_price' => 1.78, 'total_price' => 67.64],
            ['date' => '2025-06-01', 'odometer' => 57500, 'liters' => 40.0, 'unit_price' => 1.86, 'total_price' => 74.40],
            ['date' => '2025-06-25', 'odometer' => 58700, 'liters' => 37.5, 'unit_price' => 1.81, 'total_price' => 67.88],
        ]);

        $fiatPunto->vehicleServices()->create([
            'vehicle_service_type_id' => $tagliando->id,
            'date' => '2025-01-10',
            'total_paid' => 150,
            'odometer' => 50000,
        ]);

        // Veicolo 2 — Alfa Romeo Giulia: odometro 80.000 → 92.500 (delta ~12.500 km > 10.000) → reminder SCATTATO
        $alfaGiulia = Vehicle::create([
            'plate_number' => 'CC456DD',
            'brand' => 'Alfa Romeo',
            'model' => 'Giulia',
            'year' => 2021,
            'purchase_date' => '2021-03-15',
        ]);

        $alfaGiulia->vehicleServiceReminders()->create([
            'vehicle_service_type_id' => $tagliando->id,
            'every' => 10000,
        ]);

        $alfaGiulia->vehicleRefuels()->createMany([
            ['date' => '2025-01-08', 'odometer' => 80000, 'liters' => 45.0, 'unit_price' => 1.82, 'total_price' => 81.90],
            ['date' => '2025-02-01', 'odometer' => 81500, 'liters' => 43.5, 'unit_price' => 1.79, 'total_price' => 77.87],
            ['date' => '2025-02-25', 'odometer' => 83200, 'liters' => 46.0, 'unit_price' => 1.85, 'total_price' => 85.10],
            ['date' => '2025-03-20', 'odometer' => 85000, 'liters' => 44.0, 'unit_price' => 1.80, 'total_price' => 79.20],
            ['date' => '2025-04-12', 'odometer' => 87000, 'liters' => 47.5, 'unit_price' => 1.83, 'total_price' => 86.93],
            ['date' => '2025-05-05', 'odometer' => 89000, 'liters' => 45.0, 'unit_price' => 1.78, 'total_price' => 80.10],
            ['date' => '2025-06-02', 'odometer' => 91000, 'liters' => 46.5, 'unit_price' => 1.86, 'total_price' => 86.49],
            ['date' => '2025-06-28', 'odometer' => 92500, 'liters' => 44.0, 'unit_price' => 1.81, 'total_price' => 79.64],
        ]);

        $alfaGiulia->vehicleServices()->create([
            'vehicle_service_type_id' => $tagliando->id,
            'date' => '2025-01-08',
            'total_paid' => 250,
            'odometer' => 80000,
        ]);
    }
}
