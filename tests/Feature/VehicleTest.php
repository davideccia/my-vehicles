<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsVehiclesList(): void
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

    public function testCreatePageIsAccessible(): void
    {
        $this->withoutVite();

        $response = $this->get('/vehicles/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('vehicles/Create'));
    }

    public function testCanStoreAVehicle(): void
    {
        $response = $this->post('/vehicles', [
            'plate_number' => 'AB123CD',
            'brand' => 'Fiat',
            'model' => 'Panda',
            'year' => 2020,
            'purchase_date' => null,
        ]);

        $response->assertRedirect('/vehicles');
        $this->assertDatabaseHas('vehicles', ['plate_number' => 'AB123CD']);
    }

    public function testStoreValidatesRequiredFields(): void
    {
        $response = $this->post('/vehicles', []);

        $response->assertSessionHasErrors(['plate_number', 'brand', 'model', 'year']);
    }

    public function testStoreValidatesUniquePlateNumber(): void
    {
        Vehicle::factory()->create(['plate_number' => 'AB123CD']);

        $response = $this->post('/vehicles', [
            'plate_number' => 'AB123CD',
            'brand' => 'BMW',
            'model' => 'Serie 3',
            'year' => 2021,
        ]);

        $response->assertSessionHasErrors('plate_number');
    }

    public function testCanUpdateAVehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->put("/vehicles/{$vehicle->id}", [
            'plate_number' => 'ZZ999ZZ',
            'brand' => 'Toyota',
            'model' => 'Yaris',
            'year' => 2022,
            'purchase_date' => null,
        ]);

        $response->assertRedirect('/vehicles');
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'plate_number' => 'ZZ999ZZ']);
    }

    public function testCanDeleteAVehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        $response = $this->delete("/vehicles/{$vehicle->id}");

        $response->assertRedirect('/vehicles');
        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }
}
