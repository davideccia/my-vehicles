<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function testDashboardIsPubliclyAccessible(): void
    {
        $this->withoutVite();

        $response = $this->get(route('vehicles.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('vehicles/Index'));
    }
}
