<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\VehicleRefuel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleRefuelTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_refuels_list(): void
    {
        $this->withoutVite();
        VehicleRefuel::factory()->count(3)->create();

        $response = $this->get('/vehicle-refuels');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Index')
            ->has('refuels.data', 3)
            ->where('refuels.meta.total', 3)
            ->where('refuels.meta.per_page', 5)
            ->has('vehicles')
            ->where('selectedVehicleId', null)
        );
    }

    public function test_index_paginates_refuels(): void
    {
        $this->withoutVite();
        VehicleRefuel::factory()->count(7)->create();

        $response = $this->get('/vehicle-refuels');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Index')
            ->has('refuels.data', 5)
            ->where('refuels.meta.total', 7)
            ->where('refuels.meta.last_page', 2)
        );
    }

    public function test_index_filters_by_vehicle(): void
    {
        $this->withoutVite();
        $vehicle = Vehicle::factory()->create();
        VehicleRefuel::factory()->count(2)->create(['vehicle_id' => $vehicle->id]);
        VehicleRefuel::factory()->create();

        $response = $this->get('/vehicle-refuels?vehicle_id='.$vehicle->id);

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Index')
            ->has('refuels.data', 2)
            ->where('refuels.meta.total', 2)
            ->where('selectedVehicleId', $vehicle->id)
        );
    }

    public function test_index_returns_selected_from_and_to_as_null_by_default(): void
    {
        $this->withoutVite();

        $response = $this->get('/vehicle-refuels');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Index')
            ->where('selectedFrom', null)
            ->where('selectedTo', null)
        );
    }

    public function test_index_filters_by_from_date(): void
    {
        $this->withoutVite();
        VehicleRefuel::factory()->create(['date' => '2024-01-10']);
        VehicleRefuel::factory()->create(['date' => '2024-03-15']);
        VehicleRefuel::factory()->create(['date' => '2024-06-01']);

        $response = $this->get('/vehicle-refuels?from=2024-03-01');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Index')
            ->has('refuels.data', 2)
            ->where('refuels.meta.total', 2)
            ->where('selectedFrom', '2024-03-01')
        );
    }

    public function test_index_filters_by_to_date(): void
    {
        $this->withoutVite();
        VehicleRefuel::factory()->create(['date' => '2024-01-10']);
        VehicleRefuel::factory()->create(['date' => '2024-03-15']);
        VehicleRefuel::factory()->create(['date' => '2024-06-01']);

        $response = $this->get('/vehicle-refuels?to=2024-03-31');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Index')
            ->has('refuels.data', 2)
            ->where('refuels.meta.total', 2)
            ->where('selectedTo', '2024-03-31')
        );
    }

    public function test_index_filters_by_from_and_to_date(): void
    {
        $this->withoutVite();
        VehicleRefuel::factory()->create(['date' => '2024-01-10']);
        VehicleRefuel::factory()->create(['date' => '2024-03-15']);
        VehicleRefuel::factory()->create(['date' => '2024-06-01']);

        $response = $this->get('/vehicle-refuels?from=2024-02-01&to=2024-04-30');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Index')
            ->has('refuels.data', 1)
            ->where('refuels.meta.total', 1)
            ->where('selectedFrom', '2024-02-01')
            ->where('selectedTo', '2024-04-30')
        );
    }

    public function test_index_combines_vehicle_and_date_filters(): void
    {
        $this->withoutVite();
        $vehicle = Vehicle::factory()->create();
        VehicleRefuel::factory()->create(['vehicle_id' => $vehicle->id, 'date' => '2024-03-15']);
        VehicleRefuel::factory()->create(['vehicle_id' => $vehicle->id, 'date' => '2024-06-01']);
        VehicleRefuel::factory()->create(['date' => '2024-03-20']);

        $response = $this->get('/vehicle-refuels?vehicle_id='.$vehicle->id.'&from=2024-01-01&to=2024-04-30');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Index')
            ->has('refuels.data', 1)
            ->where('refuels.meta.total', 1)
            ->where('selectedVehicleId', $vehicle->id)
            ->where('selectedFrom', '2024-01-01')
            ->where('selectedTo', '2024-04-30')
        );
    }

    public function test_create_page_is_accessible(): void
    {
        $this->withoutVite();

        $response = $this->get('/vehicle-refuels/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Create')
            ->has('vehicles')
        );
    }

    public function test_can_store_a_refuel(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->post('/vehicle-refuels', [
            'vehicle_id' => $vehicle->id,
            'date' => '2024-06-15',
            'total_price' => 75.50,
            'unit_price' => 1.789,
            'liters' => 42.21,
            'odometer' => 50000,
        ]);

        $response->assertRedirect('/vehicle-refuels');
        $this->assertDatabaseHas('vehicle_refuels', [
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post('/vehicle-refuels', []);

        $response->assertSessionHasErrors(['vehicle_id', 'date', 'total_price', 'unit_price', 'liters', 'odometer']);
    }

    public function test_store_validates_vehicle_exists(): void
    {
        $response = $this->post('/vehicle-refuels', [
            'vehicle_id' => 'non-existent-uuid',
            'date' => '2024-06-15',
            'total_price' => 75.50,
            'unit_price' => 1.789,
            'liters' => 42.21,
            'odometer' => 50000,
        ]);

        $response->assertSessionHasErrors('vehicle_id');
    }

    public function test_edit_page_is_accessible(): void
    {
        $this->withoutVite();
        $refuel = VehicleRefuel::factory()->create();

        $response = $this->get("/vehicle-refuels/{$refuel->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-refuels/Edit')
            ->has('refuel')
            ->has('vehicles')
        );
    }

    public function test_can_update_a_refuel(): void
    {
        $refuel = VehicleRefuel::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $response = $this->put("/vehicle-refuels/{$refuel->id}", [
            'vehicle_id' => $vehicle->id,
            'date' => '2024-12-01',
            'total_price' => 90.00,
            'unit_price' => 1.850,
            'liters' => 48.65,
            'odometer' => 75000,
        ]);

        $response->assertRedirect('/vehicle-refuels');
        $this->assertDatabaseHas('vehicle_refuels', [
            'id' => $refuel->id,
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_can_delete_a_refuel(): void
    {
        $refuel = VehicleRefuel::factory()->create();

        $response = $this->delete("/vehicle-refuels/{$refuel->id}");

        $response->assertRedirect('/vehicle-refuels');
        $this->assertDatabaseMissing('vehicle_refuels', ['id' => $refuel->id]);
    }
}
