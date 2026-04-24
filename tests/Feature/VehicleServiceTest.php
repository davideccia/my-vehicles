<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\VehicleService;
use App\Models\VehicleServiceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_services_list(): void
    {
        $this->withoutVite();
        VehicleService::factory()->count(3)->create();

        $response = $this->get('/vehicle-services');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Index')
            ->has('services.data', 3)
            ->where('services.meta.total', 3)
            ->where('services.meta.per_page', 5)
            ->has('vehicles')
            ->where('selectedVehicleId', null)
        );
    }

    public function test_index_paginates_services(): void
    {
        $this->withoutVite();
        VehicleService::factory()->count(7)->create();

        $response = $this->get('/vehicle-services');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Index')
            ->has('services.data', 5)
            ->where('services.meta.total', 7)
            ->where('services.meta.last_page', 2)
        );
    }

    public function test_index_filters_by_vehicle(): void
    {
        $this->withoutVite();
        $vehicle = Vehicle::factory()->create();
        VehicleService::factory()->count(2)->create(['vehicle_id' => $vehicle->id]);
        VehicleService::factory()->create();

        $response = $this->get('/vehicle-services?vehicle_id='.$vehicle->id);

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Index')
            ->has('services.data', 2)
            ->where('services.meta.total', 2)
            ->where('selectedVehicleId', $vehicle->id)
        );
    }

    public function test_index_returns_selected_from_and_to_as_null_by_default(): void
    {
        $this->withoutVite();

        $response = $this->get('/vehicle-services');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Index')
            ->where('selectedFrom', null)
            ->where('selectedTo', null)
        );
    }

    public function test_index_filters_by_from_date(): void
    {
        $this->withoutVite();
        VehicleService::factory()->create(['date' => '2024-01-10']);
        VehicleService::factory()->create(['date' => '2024-03-15']);
        VehicleService::factory()->create(['date' => '2024-06-01']);

        $response = $this->get('/vehicle-services?from=2024-03-01');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Index')
            ->has('services.data', 2)
            ->where('services.meta.total', 2)
            ->where('selectedFrom', '2024-03-01')
        );
    }

    public function test_index_filters_by_to_date(): void
    {
        $this->withoutVite();
        VehicleService::factory()->create(['date' => '2024-01-10']);
        VehicleService::factory()->create(['date' => '2024-03-15']);
        VehicleService::factory()->create(['date' => '2024-06-01']);

        $response = $this->get('/vehicle-services?to=2024-03-31');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Index')
            ->has('services.data', 2)
            ->where('services.meta.total', 2)
            ->where('selectedTo', '2024-03-31')
        );
    }

    public function test_index_filters_by_from_and_to_date(): void
    {
        $this->withoutVite();
        VehicleService::factory()->create(['date' => '2024-01-10']);
        VehicleService::factory()->create(['date' => '2024-03-15']);
        VehicleService::factory()->create(['date' => '2024-06-01']);

        $response = $this->get('/vehicle-services?from=2024-02-01&to=2024-04-30');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Index')
            ->has('services.data', 1)
            ->where('services.meta.total', 1)
            ->where('selectedFrom', '2024-02-01')
            ->where('selectedTo', '2024-04-30')
        );
    }

    public function test_index_combines_vehicle_and_date_filters(): void
    {
        $this->withoutVite();
        $vehicle = Vehicle::factory()->create();
        VehicleService::factory()->create(['vehicle_id' => $vehicle->id, 'date' => '2024-03-15']);
        VehicleService::factory()->create(['vehicle_id' => $vehicle->id, 'date' => '2024-06-01']);
        VehicleService::factory()->create(['date' => '2024-03-20']);

        $response = $this->get('/vehicle-services?vehicle_id='.$vehicle->id.'&from=2024-01-01&to=2024-04-30');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Index')
            ->has('services.data', 1)
            ->where('services.meta.total', 1)
            ->where('selectedVehicleId', $vehicle->id)
            ->where('selectedFrom', '2024-01-01')
            ->where('selectedTo', '2024-04-30')
        );
    }

    public function test_create_page_is_accessible(): void
    {
        $this->withoutVite();

        $response = $this->get('/vehicle-services/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Create')
            ->has('vehicles')
            ->has('serviceTypes')
        );
    }

    public function test_can_store_a_service(): void
    {
        $vehicle = Vehicle::factory()->create();
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->post('/vehicle-services', [
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'date' => '2024-06-15',
            'total_paid' => 120.00,
            'odometer' => 50000,
            'location' => 'Roma',
            'notes' => 'Tutto ok',
        ]);

        $response->assertRedirect('/vehicle-services');
        $this->assertDatabaseHas('vehicle_services', [
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post('/vehicle-services', []);

        $response->assertSessionHasErrors(['vehicle_id', 'vehicle_service_type_id', 'date', 'total_paid', 'odometer']);
    }

    public function test_store_validates_vehicle_exists(): void
    {
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->post('/vehicle-services', [
            'vehicle_id' => 'non-existent-uuid',
            'vehicle_service_type_id' => $serviceType->id,
            'date' => '2024-06-15',
            'total_paid' => 120.00,
            'odometer' => 50000,
        ]);

        $response->assertSessionHasErrors('vehicle_id');
    }

    public function test_store_validates_service_type_exists(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->post('/vehicle-services', [
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => 'non-existent-uuid',
            'date' => '2024-06-15',
            'total_paid' => 120.00,
            'odometer' => 50000,
        ]);

        $response->assertSessionHasErrors('vehicle_service_type_id');
    }

    public function test_edit_page_is_accessible(): void
    {
        $this->withoutVite();
        $service = VehicleService::factory()->create();

        $response = $this->get("/vehicle-services/{$service->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-services/Edit')
            ->has('service')
            ->has('vehicles')
            ->has('serviceTypes')
        );
    }

    public function test_can_update_a_service(): void
    {
        $service = VehicleService::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->put("/vehicle-services/{$service->id}", [
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'date' => '2024-12-01',
            'total_paid' => 250.00,
            'odometer' => 75000,
            'location' => null,
            'notes' => null,
        ]);

        $response->assertRedirect('/vehicle-services');
        $this->assertDatabaseHas('vehicle_services', [
            'id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
        ]);
    }

    public function test_can_delete_a_service(): void
    {
        $service = VehicleService::factory()->create();

        $response = $this->delete("/vehicle-services/{$service->id}");

        $response->assertRedirect('/vehicle-services');
        $this->assertDatabaseMissing('vehicle_services', ['id' => $service->id]);
    }

    public function test_deleting_vehicle_cascades_to_services(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehicleService::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->delete("/vehicles/{$vehicle->id}");

        $this->assertDatabaseMissing('vehicle_services', ['vehicle_id' => $vehicle->id]);
    }

    public function test_deleting_service_type_cascades_to_services(): void
    {
        $serviceType = VehicleServiceType::factory()->create();
        VehicleService::factory()->create(['vehicle_service_type_id' => $serviceType->id]);

        $this->delete("/vehicle-service-types/{$serviceType->id}");

        $this->assertDatabaseMissing('vehicle_services', ['vehicle_service_type_id' => $serviceType->id]);
    }
}
