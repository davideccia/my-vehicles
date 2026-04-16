# Testing Patterns

**Analysis Date:** 2025-04-16

## Test Framework

**Runner:**
- **PHPUnit** 11.x
- Config: `phpunit.xml`
- Test environment: SQLite in-memory database (`:memory:`)

**Assertion Library:**
- PHPUnit built-in assertions
- Inertia-specific assertions: `assertInertia()`

**Run Commands:**
```bash
php artisan test --compact                          # Run all tests
php artisan test --compact tests/Feature/Foo.php   # Run specific test file
php artisan test --compact --filter=testName        # Run test by name
```

## Test File Organization

**Location:**
- Feature tests: `tests/Feature/`
- Unit tests: `tests/Unit/`

**Naming:**
- Pattern: `{ResourceName}Test.php`
- Examples: `VehicleTest.php`, `VehicleRefuelTest.php`, `VehicleServiceReminderTest.php`
- Test methods: `test_descriptive_action()` or `testDescriptiveAction()` (both snake_case and camelCase used)

**Structure:**
```
tests/
├── Feature/
│   ├── VehicleTest.php
│   ├── VehicleRefuelTest.php
│   ├── VehicleServiceTest.php
│   ├── VehicleServiceTypeTest.php
│   ├── VehicleServiceReminderTest.php
│   ├── SettingsTest.php
│   ├── ReportTest.php
│   ├── DashboardTest.php
│   └── ExampleTest.php
├── Unit/
│   └── ExampleTest.php
└── TestCase.php
```

## Test Structure

**Suite Organization:**
All feature tests extend `Tests\TestCase` and use `RefreshDatabase` trait:

```php
<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function testCanStoreAVehicle(): void
    {
        // Arrange: Create test data
        // Act: Perform HTTP request
        // Assert: Verify response and database state
    }
}
```

**Patterns:**
- `use RefreshDatabase;` — Rolls back database to fresh state after each test
- `$this->withoutVite();` — Disables Vite dev server output during page render tests
- Tests do NOT use Vite when testing page component output
- `RefreshDatabase` ensures test isolation without persisting state

## Test Types

**Feature Tests (Integration):**
- HTTP endpoint testing via `$this->get()`, `$this->post()`, `$this->put()`, `$this->delete()`
- Response assertions: `assertOk()`, `assertRedirect()`, `assertSessionHasErrors()`
- Inertia component assertions: `assertInertia(fn ($page) => $page->component(...)->has(...)->where(...))`
- Database state assertions: `assertDatabaseHas()`, `assertDatabaseMissing()`

**Examples from codebase:**

**VehicleTest.php - Index List Rendering:**
```php
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
```

**VehicleTest.php - Store with Validation:**
```php
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
```

**VehicleTest.php - Field Validation:**
```php
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
```

**VehicleServiceReminderTest.php - Relationship Cascade:**
```php
public function test_deleting_vehicle_cascades_to_reminders(): void
{
    $vehicle = Vehicle::factory()->create();
    VehicleServiceReminder::factory()->create(['vehicle_id' => $vehicle->id]);

    $this->delete("/vehicles/{$vehicle->id}");

    $this->assertDatabaseMissing('vehicle_service_reminders', ['vehicle_id' => $vehicle->id]);
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
```

**VehicleRefuelTest.php - Query Filter Testing:**
```php
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
        ->has('refuels', 2)
        ->where('selectedVehicleId', $vehicle->id)
    );
}
```

**Unit Tests:**
- Minimal unit test examples present (e.g., `ExampleTest.php` is a stub)
- Most tests are feature/integration focused
- Individual methods (e.g., `isOverDue()`, `currentOdometer()`) tested indirectly via feature tests

**E2E Tests:**
- Not present in codebase
- No Playwright or Cypress configuration found

## Fixtures and Factories

**Test Data (PHP):**
- Laravel factories auto-generated for each model
- Factory usage: `Vehicle::factory()->create()`, `Vehicle::factory()->count(3)->create()`
- Factories apply all required defaults and attribute overrides

**Examples:**
```php
// Basic creation
$vehicle = Vehicle::factory()->create();

// With attribute override
$vehicle = Vehicle::factory()->create(['plate_number' => 'AB123CD']);

// Multiple records
$refuels = VehicleRefuel::factory()->count(3)->create();

// Related data
$reminder = VehicleServiceReminder::factory()->create([
    'vehicle_id' => $vehicle->id,
    'vehicle_service_type_id' => $serviceType->id,
    'every' => 10000,
]);
```

**Location:**
- Factories auto-created in `database/factories/` (auto-loaded by Laravel)
- Not explicitly visible in codebase but referenced in tests

## Coverage

**Requirements:** Not explicitly enforced in config
- `phpunit.xml` includes `<source>` directive covering `app/` directory
- No coverage thresholds or reporting configuration

**View Coverage:**
```bash
# Use built-in PHPUnit coverage (requires pcov or xdebug)
php artisan test --coverage --coverage-html=coverage
```

## Common Patterns

**Arrange-Act-Assert (AAA):**
All tests follow implicit AAA structure:
- **Arrange**: Create test fixtures via factories
- **Act**: Perform HTTP request or method call
- **Assert**: Verify response, database state, or component props

**Testing State Transitions:**

```php
// VehicleTest.php: Update and verify state change
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
```

**Testing Computed Properties:**

```php
// VehicleServiceReminderTest.php: Test isOverDue calculation
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
```

**Testing Cascading Deletes:**
- Verify parent deletion cascades to children
- Verify nullification on conditional deletes
- Tests in `VehicleServiceReminderTest.php`

**Testing with Page Rendering:**
- Use `withoutVite()` to suppress Vite logs when asserting Inertia components
- Do NOT use Vite in test environment
- Test database state, not HTML output

## Test Database Setup

**Configuration (phpunit.xml):**
```xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="CACHE_STORE" value="array"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="SESSION_DRIVER" value="array"/>
```

**Implications:**
- Tests run against in-memory SQLite database
- No test database file persists
- Each test gets fresh database via `RefreshDatabase` trait
- Fast test execution (no I/O latency)
- Email/queue operations use synchronous drivers

## Test Naming Conventions

**Observed patterns:**
- Snake case with `test_` prefix: `test_index_returns_reminders_list()`
- Camel case with `test` prefix: `testCanStoreAVehicle()`
- Both styles used interchangeably; prefer snake_case for new tests
- Descriptive names that read like sentences: `test_deleting_vehicle_cascades_to_reminders()`

## Running Tests

**All tests:**
```bash
php artisan test --compact
# or
./vendor/bin/phpunit
```

**Specific file:**
```bash
php artisan test --compact tests/Feature/VehicleTest.php
```

**Watch mode (if configured):**
```bash
php artisan test --watch
```

**Filter by name:**
```bash
php artisan test --compact --filter=testCanStoreAVehicle
```

---

*Testing analysis: 2025-04-16*
