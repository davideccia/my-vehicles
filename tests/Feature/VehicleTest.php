<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_vehicles_list(): void
    {
        $this->withoutVite();
        Vehicle::factory()->count(3)->create();

        $response = $this->get('/vehicles');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('vehicles/Index')
            ->has('vehicles', 3)
        );
    }

    public function test_create_page_is_accessible(): void
    {
        $this->withoutVite();

        $response = $this->get('/vehicles/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('vehicles/Create'));
    }

    public function test_can_store_a_vehicle(): void
    {
        $response = $this->post('/vehicles', [
            'plate_number' => 'AB123CD',
            'brand' => 'Fiat',
            'model' => 'Panda',
            'year' => 2020,
            'purchase_date' => null,
            'color' => '#FF0000',
        ]);

        $response->assertRedirect('/vehicles');
        $this->assertDatabaseHas('vehicles', ['plate_number' => 'AB123CD']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post('/vehicles', []);

        $response->assertSessionHasErrors(['plate_number', 'brand', 'model', 'year']);
    }

    public function test_store_validates_unique_plate_number(): void
    {
        Vehicle::factory()->create(['plate_number' => 'AB123CD']);

        $response = $this->post('/vehicles', [
            'plate_number' => 'AB123CD',
            'brand' => 'BMW',
            'model' => 'Serie 3',
            'year' => 2021,
            'color' => '#FF0000',
        ]);

        $response->assertSessionHasErrors('plate_number');
    }

    public function test_can_update_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->put("/vehicles/{$vehicle->id}", [
            'plate_number' => 'ZZ999ZZ',
            'brand' => 'Toyota',
            'model' => 'Yaris',
            'year' => 2022,
            'purchase_date' => null,
            'color' => '#FF0000',
        ]);

        $response->assertRedirect('/vehicles');
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'plate_number' => 'ZZ999ZZ']);
    }

    public function test_can_delete_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->delete("/vehicles/{$vehicle->id}");

        $response->assertRedirect('/vehicles');
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }
}
