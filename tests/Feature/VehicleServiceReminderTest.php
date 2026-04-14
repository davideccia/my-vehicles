<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\VehicleRefuel;
use App\Models\VehicleService;
use App\Models\VehicleServiceReminder;
use App\Models\VehicleServiceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleServiceReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_reminders_list(): void
    {
        $this->withoutVite();
        VehicleServiceReminder::factory()->count(3)->create();

        $response = $this->get('/vehicle-service-reminders');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-reminders/Index')
            ->has('reminders', 3)
            ->has('vehicles')
            ->where('selectedVehicleId', null)
        );
    }

    public function test_index_filters_by_vehicle(): void
    {
        $this->withoutVite();
        $vehicle = Vehicle::factory()->create();
        VehicleServiceReminder::factory()->count(2)->create(['vehicle_id' => $vehicle->id]);
        VehicleServiceReminder::factory()->create();

        $response = $this->get('/vehicle-service-reminders?vehicle_id='.$vehicle->id);

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-reminders/Index')
            ->has('reminders', 2)
            ->where('selectedVehicleId', $vehicle->id)
        );
    }

    public function test_create_page_is_accessible(): void
    {
        $this->withoutVite();

        $response = $this->get('/vehicle-service-reminders/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-reminders/Create')
            ->has('vehicles')
            ->has('serviceTypes')
        );
    }

    public function test_can_store_a_reminder(): void
    {
        $vehicle = Vehicle::factory()->create();
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->post('/vehicle-service-reminders', [
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'every' => 10000,
        ]);

        $response->assertRedirect('/vehicle-service-reminders');
        $this->assertDatabaseHas('vehicle_service_reminders', [
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'every' => 10000,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post('/vehicle-service-reminders', []);

        $response->assertSessionHasErrors(['vehicle_id', 'vehicle_service_type_id', 'every']);
    }

    public function test_store_validates_every_min_1(): void
    {
        $vehicle = Vehicle::factory()->create();
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->post('/vehicle-service-reminders', [
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'every' => 0,
        ]);

        $response->assertSessionHasErrors('every');
    }

    public function test_store_validates_vehicle_exists(): void
    {
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->post('/vehicle-service-reminders', [
            'vehicle_id' => 'non-existent-uuid',
            'vehicle_service_type_id' => $serviceType->id,
            'every' => 10000,
        ]);

        $response->assertSessionHasErrors('vehicle_id');
    }

    public function test_store_validates_service_type_exists(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->post('/vehicle-service-reminders', [
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => 'non-existent-uuid',
            'every' => 10000,
        ]);

        $response->assertSessionHasErrors('vehicle_service_type_id');
    }

    public function test_edit_page_is_accessible(): void
    {
        $this->withoutVite();
        $reminder = VehicleServiceReminder::factory()->create();

        $response = $this->get("/vehicle-service-reminders/{$reminder->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-reminders/Edit')
            ->has('reminder')
            ->has('vehicles')
            ->has('serviceTypes')
        );
    }

    public function test_can_update_a_reminder(): void
    {
        $reminder = VehicleServiceReminder::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $serviceType = VehicleServiceType::factory()->create();

        $response = $this->put("/vehicle-service-reminders/{$reminder->id}", [
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'every' => 15000,
        ]);

        $response->assertRedirect('/vehicle-service-reminders');
        $this->assertDatabaseHas('vehicle_service_reminders', [
            'id' => $reminder->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'every' => 15000,
        ]);
    }

    public function test_can_delete_a_reminder(): void
    {
        $reminder = VehicleServiceReminder::factory()->create();

        $response = $this->delete("/vehicle-service-reminders/{$reminder->id}");

        $response->assertRedirect('/vehicle-service-reminders');
        $this->assertDatabaseMissing('vehicle_service_reminders', ['id' => $reminder->id]);
    }

    public function test_deleting_vehicle_cascades_to_reminders(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehicleServiceReminder::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->delete("/vehicles/{$vehicle->id}");

        $this->assertDatabaseMissing('vehicle_service_reminders', ['vehicle_id' => $vehicle->id]);
    }

    public function test_deleting_service_type_cascades_to_reminders(): void
    {
        $serviceType = VehicleServiceType::factory()->create();
        VehicleServiceReminder::factory()->create(['vehicle_service_type_id' => $serviceType->id]);

        $this->delete("/vehicle-service-types/{$serviceType->id}");

        $this->assertDatabaseMissing('vehicle_service_reminders', ['vehicle_service_type_id' => $serviceType->id]);
    }

    public function test_deleting_reminder_nullifies_vehicle_service_reference(): void
    {
        $reminder = VehicleServiceReminder::factory()->create();
        $service = VehicleService::factory()->create(['vehicle_service_reminder_id' => $reminder->id]);

        $this->delete("/vehicle-service-reminders/{$reminder->id}");

        $this->assertDatabaseHas('vehicle_services', [
            'id' => $service->id,
            'vehicle_service_reminder_id' => null,
        ]);
    }

    public function test_index_includes_is_overdue_false_when_not_yet_due(): void
    {
        $this->withoutVite();
        $vehicle = Vehicle::factory()->create();
        $serviceType = VehicleServiceType::factory()->create();
        $reminder = VehicleServiceReminder::factory()->create([
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'every' => 10000,
        ]);
        VehicleService::factory()->create([
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'vehicle_service_reminder_id' => $reminder->id,
            'odometer' => 5000,
            'date' => '2024-01-01',
        ]);
        VehicleRefuel::factory()->create([
            'vehicle_id' => $vehicle->id,
            'odometer' => 8000,
            'date' => '2024-06-01',
        ]);

        $response = $this->get('/vehicle-service-reminders');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-reminders/Index')
            ->where('reminders.0.is_overdue', false)
        );
    }

    public function test_index_includes_is_overdue_true_when_overdue(): void
    {
        $this->withoutVite();
        $vehicle = Vehicle::factory()->create();
        $serviceType = VehicleServiceType::factory()->create();
        $reminder = VehicleServiceReminder::factory()->create([
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'every' => 10000,
        ]);
        VehicleService::factory()->create([
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'vehicle_service_reminder_id' => $reminder->id,
            'odometer' => 5000,
            'date' => '2024-01-01',
        ]);
        VehicleRefuel::factory()->create([
            'vehicle_id' => $vehicle->id,
            'odometer' => 16000,
            'date' => '2024-06-01',
        ]);

        $response = $this->get('/vehicle-service-reminders');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-reminders/Index')
            ->where('reminders.0.is_overdue', true)
        );
    }

    public function test_index_includes_is_overdue_false_when_no_previous_services(): void
    {
        $this->withoutVite();
        $vehicle = Vehicle::factory()->create();
        $serviceType = VehicleServiceType::factory()->create();
        VehicleServiceReminder::factory()->create([
            'vehicle_id' => $vehicle->id,
            'vehicle_service_type_id' => $serviceType->id,
            'every' => 10000,
        ]);
        VehicleRefuel::factory()->create([
            'vehicle_id' => $vehicle->id,
            'odometer' => 9000,
            'date' => '2024-06-01',
        ]);

        $response = $this->get('/vehicle-service-reminders');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicle-service-reminders/Index')
            ->where('reminders.0.is_overdue', false)
        );
    }
}
