<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function testReturnsASuccessfulResponse()
    {
        $this->withoutVite();
        $response = $this->get(route('vehicles.index'));

        $response->assertOk();
    }
}
