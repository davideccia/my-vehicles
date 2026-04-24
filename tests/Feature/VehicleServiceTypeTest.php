<?php

namespace Tests\Feature;

use App\Models\VehicleServiceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleServiceTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_service_types_list(): void
    {
        $this->withoutVite();
        VehicleServiceType::factory()->count(3)->create();

        $response = $this->get('/vehicle-service-types');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-types/Index')
            ->has('serviceTypes.data', 3)
            ->where('serviceTypes.meta.total', 3)
            ->where('serviceTypes.meta.per_page', 5)
        );
    }

    public function test_index_paginates_service_types(): void
    {
        $this->withoutVite();
        VehicleServiceType::factory()->count(7)->create();

        $response = $this->get('/vehicle-service-types');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-types/Index')
            ->has('serviceTypes.data', 5)
            ->where('serviceTypes.meta.total', 7)
            ->where('serviceTypes.meta.last_page', 2)
        );
    }

    public function test_create_page_is_accessible(): void
    {
        $this->withoutVite();

        $response = $this->get('/vehicle-service-types/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-types/Create')
        );
    }

    public function test_can_store_a_service_type(): void
    {
        $response = $this->post('/vehicle-service-types', [
            'icon' => 'Wrench',
            'label' => 'Tagliando',
        ]);

        $response->assertRedirect('/vehicle-service-types');
        $this->assertDatabaseHas('vehicle_service_types', [
            'icon' => 'Wrench',
            'label' => 'Tagliando',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post('/vehicle-service-types', []);

        $response->assertSessionHasErrors(['icon', 'label']);
    }

    public function test_edit_page_is_accessible(): void
    {
        $this->withoutVite();
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->get("/vehicle-service-types/{$serviceType->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-types/Edit')
            ->has('serviceType')
        );
    }

    public function test_can_update_a_service_type(): void
    {
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->put("/vehicle-service-types/{$serviceType->id}", [
            'icon' => 'Gauge',
            'label' => 'Pneumatici',
        ]);

        $response->assertRedirect('/vehicle-service-types');
        $this->assertDatabaseHas('vehicle_service_types', [
            'id' => $serviceType->id,
            'icon' => 'Gauge',
            'label' => 'Pneumatici',
        ]);
    }

    public function test_can_delete_a_service_type(): void
    {
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->delete("/vehicle-service-types/{$serviceType->id}");

        $response->assertRedirect('/vehicle-service-types');
        $this->assertDatabaseMissing('vehicle_service_types', ['id' => $serviceType->id]);
    }
}
