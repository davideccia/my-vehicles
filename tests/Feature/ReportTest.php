<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\VehicleRefuel;
use App\Services\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_fuel_costs_only_includes_dates_with_refuels(): void
    {
        $vehicle = Vehicle::factory()->create();

        VehicleRefuel::factory()->create([
            'vehicle_id' => $vehicle->id,
            'date' => '2024-01-05',
            'unit_price' => 1.80,
        ]);

        VehicleRefuel::factory()->create([
            'vehicle_id' => $vehicle->id,
            'date' => '2024-01-15',
            'unit_price' => 1.90,
        ]);

        $result = Report::fuelCosts('2024-01-01', '2024-01-31', $vehicle);

        // Only 2 labels, not 31
        $this->assertCount(2, $result['labels']);
        $this->assertCount(1, $result['datasets']);
        $this->assertCount(2, $result['datasets'][0]['data']);
    }

    public function test_fuel_costs_returns_zero_for_vehicle_missing_on_shared_date(): void
    {
        $vehicle1 = Vehicle::factory()->create();
        $vehicle2 = Vehicle::factory()->create();

        VehicleRefuel::factory()->create([
            'vehicle_id' => $vehicle1->id,
            'date' => '2024-01-05',
            'unit_price' => 1.80,
        ]);

        VehicleRefuel::factory()->create([
            'vehicle_id' => $vehicle2->id,
            'date' => '2024-01-10',
            'unit_price' => 1.90,
        ]);

        $result = Report::fuelCosts('2024-01-01', '2024-01-31');

        // 2 dates total (Jan 5 and Jan 10)
        $this->assertCount(2, $result['labels']);
        $this->assertCount(2, $result['datasets']);

        // Each vehicle has data for both dates, with 0 where no refuel happened
        $dataByLabel = collect($result['datasets'])->keyBy('label');
        $data1 = $dataByLabel[$vehicle1->full_name]['data'];
        $data2 = $dataByLabel[$vehicle2->full_name]['data'];

        // vehicle1 has refuel on date index 0, not on 1
        $this->assertNotEquals(0, $data1[0]);
        $this->assertEquals(0, $data1[1]);

        // vehicle2 has refuel on date index 1, not on 0
        $this->assertEquals(0, $data2[0]);
        $this->assertNotEquals(0, $data2[1]);
    }

    public function test_fuel_costs_returns_empty_when_no_refuels(): void
    {
        Vehicle::factory()->create();

        $result = Report::fuelCosts('2024-01-01', '2024-01-31');

        $this->assertCount(0, $result['labels']);
        $this->assertCount(0, $result['datasets']);
    }

    public function test_fuel_costs_filters_by_vehicle(): void
    {
        $vehicle1 = Vehicle::factory()->create();
        $vehicle2 = Vehicle::factory()->create();

        VehicleRefuel::factory()->create(['vehicle_id' => $vehicle1->id, 'date' => '2024-01-05']);
        VehicleRefuel::factory()->create(['vehicle_id' => $vehicle2->id, 'date' => '2024-01-10']);

        $result = Report::fuelCosts('2024-01-01', '2024-01-31', $vehicle1);

        $this->assertCount(1, $result['labels']);
        $this->assertCount(1, $result['datasets']);
        $this->assertSame($vehicle1->full_name, $result['datasets'][0]['label']);
    }
}
