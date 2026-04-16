# Codebase Concerns

**Analysis Date:** 2026-04-16

## Missing Authentication & Authorization

**Issue:** No authentication middleware protecting any routes. All CRUD endpoints are publicly accessible without login.

**Files:** `routes/web.php`, `bootstrap/app.php`

**Impact:** 
- Any user can access, modify, or delete all vehicle data
- No multi-user isolation — all data is shared globally
- Settings endpoints modify cookies without user context
- Critical for a personal vehicle management app that stores user data

**Fix approach:**
- Add `auth` middleware to all routes that should require authentication
- Implement user scoping so each user only sees their own vehicles
- Add policies to restrict vehicle/refuel/service ownership
- Consider adding two-factor authentication given the app handles personal records

---

## N+1 Query Issues in Resource Loading

**Issue:** The `VehicleServiceReminderResource` calls `latestVehicleServiceOdometer()` and related methods during serialization, which trigger database queries for every reminder in a list.

**Files:** 
- `app/Http/Resources/VehicleServiceReminderResource.php` (lines 24-28)
- `app/Models/VehicleServiceReminder.php` (lines 29-46)

**Pattern:**
```php
// In Resource toArray() - called once per reminder
'last_vehicle_service_odometer' => $this->resource->latestVehicleServiceOdometer(),
'recommended_vehicle_service_odometer' => $this->resource->recommendedVehicleServiceOdometer(),
'overdue_odometer_diff' => $this->resource->overdueOdometerDiff(),
'is_overdue' => $this->resource->isOverDue(),

// Each method queries VehicleService table
public function latestVehicleServiceOdometer(): int {
    return VehicleService::query()
        ->where('vehicle_services.vehicle_id', $this->vehicle_id)
        ->where('vehicle_services.vehicle_service_type_id', $this->vehicle_service_type_id)
        ->orderByDesc('vehicle_services.date')
        ->first()?->odometer ?? 0;
}
```

**Impact:** 
- 100 reminders on index page = 400+ extra database queries
- Performance degrades with data volume
- Unnecessary database load

**Fix approach:**
- Eager load `VehicleService` data with a custom query that pre-calculates the latest odometer per reminder
- Move calculation logic to a dedicated service or query builder
- Use `select()` to limit columns fetched
- Consider caching results if reminders don't change frequently

---

## Missing Database Indexes

**Issue:** Foreign key columns used in filters lack indexes. Common query patterns on `vehicle_id`, `vehicle_service_type_id` are unindexed.

**Files:**
- `database/migrations/2026_03_31_135313_create_vehicle_refuels_table.php` (no index on vehicle_id)
- `database/migrations/2026_03_31_142328_create_vehicle_services_table.php` (no indexes beyond FK)
- `database/migrations/2026_04_02_114015_create_vehicle_service_reminders_table.php` (no indexes beyond FK)

**Pattern:** Controllers filter by `vehicle_id`:
```php
if ($request->filled('vehicle_id')) {
    $query->where('vehicle_id', $request->vehicle_id);
}
```

**Impact:**
- Full table scans on filter queries
- Slow index pages as data grows
- Preventable performance cliff at ~10k records

**Fix approach:**
- Add index on `vehicle_id` to `vehicle_refuels`, `vehicle_services`, `vehicle_service_reminders`
- Add composite index on `(vehicle_id, vehicle_service_type_id)` to `vehicle_services` and `vehicle_service_reminders` for lookup efficiency
- Add index on `date` columns for sorting operations

---

## Validation Gaps: Odometer Regression

**Issue:** No validation prevents creating or updating refuel/service records with odometers lower than previous records. This corrupts the `currentOdometer()` calculation and reminder overdue status.

**Files:**
- `app/Http/Controllers/VehicleRefuelController.php` (lines 41-48)
- `app/Http/Controllers/VehicleServiceController.php` (lines 44-52)

**Pattern:**
```php
$validated = $request->validate([
    'odometer' => ['required', 'integer', 'min:0'],  // Only min:0, no max or progression check
]);
```

**Impact:**
- User can record service at 100k km, then refuel at 50k km
- `currentOdometer()` returns the max, which may miss genuine history
- Reminder `isOverDue()` produces incorrect results
- Corrupts data integrity

**Fix approach:**
- Add custom validation rule: odometer must be >= the vehicle's current odometer (calculated from latest service/refuel)
- Validate in a FormRequest or custom rule
- Consider soft-rejecting (warning but allowing) if business logic permits historical corrections

---

## Settings Stored as Cookies Without Encryption

**Issue:** User preferences (locale, color, theme) are stored in unencrypted cookies, except for specific carve-outs in the middleware.

**Files:** `bootstrap/app.php` (line 18), `app/Http/Controllers/SettingsController.php`

**Pattern:**
```php
// bootstrap/app.php
$middleware->encryptCookies(except: ['appearance', 'sidebar_state', 'locale', 'primary_color', 'color_scheme']);

// SettingsController
return redirect()->back()->withCookie(
    cookie()->forever('primary_color', $validated['color'])
);
```

**Impact:**
- Cookies transmitted in plaintext over HTTP (if not HTTPS)
- User preferences exposed in network traffic
- If migrating to authentication, settings are not tied to user identity

**Fix approach:**
- Move user settings to the `users` table once authentication is added
- Remove the exception list from `encryptCookies` (allow encryption)
- Keep only truly non-sensitive, framework-level cookies unencrypted

---

## No Rate Limiting on State-Changing Endpoints

**Issue:** POST/PUT/DELETE endpoints have no rate limiting. A user could spam create/update/delete requests unchecked.

**Files:** All controllers (`VehicleController`, `VehicleServiceController`, etc.)

**Impact:**
- API abuse / DoS vector
- Database bloat from spam submissions
- No throttling on form resubmissions

**Fix approach:**
- Add `throttle:60,1` middleware to routes (or appropriate rate)
- Use `RateLimited` attribute on routes if using newer Laravel routing
- Consider per-user rate limits once authentication is added

---

## Unprotected Filter Parameters

**Issue:** Controller filters accept arbitrary `vehicle_id` from the request without verifying the current user owns that vehicle (once auth is added).

**Files:**
- `app/Http/Controllers/VehicleServiceReminderController.php` (line 28)
- `app/Http/Controllers/VehicleRefuelController.php` (line 22)
- `app/Http/Controllers/VehicleServiceController.php` (line 24)

**Pattern:**
```php
if ($request->filled('vehicle_id')) {
    $query->where('vehicle_id', $request->vehicle_id);
}
```

**Impact:**
- Once auth is added, users could filter/view other users' vehicles by guessing UUIDs
- No scope isolation

**Fix approach:**
- Once auth is added, automatically scope queries to `auth()->user()->vehicles()`
- Never trust `vehicle_id` from request; verify ownership first
- Add policy checks to `show`/`edit`/`delete` endpoints

---

## No Error Handling or Logging

**Issue:** No custom error handlers or logging for failures. Exceptions bubble up unhandled.

**Files:** `bootstrap/app.php` (line 27-28 is empty), all controllers

**Pattern:**
```php
->withExceptions(function (Exceptions $exceptions): void {
    //
})->create();
```

**Impact:**
- User sees raw Laravel error pages with stack traces (information disclosure in production)
- No server-side logs of failures
- No alerting on critical failures (cascade deletes, validation errors, database outages)

**Fix approach:**
- Add exception rendering for 404/403/500 errors with user-friendly messages
- Log all failed validations and server errors
- Send alerts for critical failures (database down, cascade delete, etc.)

---

## Cascade Delete Risks

**Issue:** Foreign key constraints cascade deletions. Deleting a vehicle or service type automatically deletes all related records without confirmation or audit trail.

**Files:**
- `database/migrations/2026_04_02_114015_create_vehicle_service_reminders_table.php` (lines 16-17)
- All similar foreign key constraints

**Pattern:**
```php
$table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
```

**Impact:**
- Deleting a vehicle silently deletes 1000s of records (refuels, services, reminders)
- No undo; no audit trail
- User cannot recover data

**Fix approach:**
- Soft-delete vehicles instead (add `deleted_at` column, use `SoftDeletes` trait)
- Require explicit confirmation before cascading deletes
- Add audit logging (created_by, deleted_by, deleted_at, reason)
- Consider archiving instead of hard-delete for historical records

---

## No Input Sanitization for Long Text Fields

**Issue:** `notes` field in services and `location` field accept arbitrary text with no max length in the database schema. This allows unbounded storage and potential injection vectors.

**Files:**
- `database/migrations/2026_03_31_142328_create_vehicle_services_table.php`
- `app/Http/Controllers/VehicleServiceController.php` (line 51 max:255 in validation, but DB has no constraint)

**Pattern:**
```php
'location' => ['nullable', 'string', 'max:255'],  // Validation only
'notes' => ['nullable', 'string'],                 // No max in validation or DB
```

**Impact:**
- Validation can be bypassed if API is called directly
- Database can grow unboundedly if notes are long
- Potential HTML/JS injection if rendered without escaping (Vue handles this, but risky)

**Fix approach:**
- Add `max:` validation rules to all text fields
- Add database constraints (e.g., `$table->text('notes')->storable()` with size limits)
- Ensure Vue templates use `{{ }}` interpolation (safe) not `v-html` (unsafe)

---

## Missing Pagination

**Issue:** All index endpoints load all records into memory with `->get()`. No pagination.

**Files:**
- `app/Http/Controllers/VehicleController.php` (line 17)
- `app/Http/Controllers/VehicleServiceReminderController.php` (line 32)
- All similar index methods

**Pattern:**
```php
return Inertia::render('vehicle-refuels/Index', [
    'refuels' => VehicleRefuelResource::collection($query->get())->resolve(),  // All records
]);
```

**Impact:**
- 10k refuel records loaded into memory on a single page
- Slow page load and high memory usage
- Frontend crashes rendering 10k+ items

**Fix approach:**
- Use `paginate(50)` or `simplePaginate(50)` instead of `->get()`
- Update Inertia response to include `paginator` metadata
- Implement infinite scroll or pagination UI in Vue

---

## Test Coverage Incomplete

**Issue:** Only 11 test files exist. Many features are untested or lightly tested.

**Files:** `tests/` directory

**Missing coverage:**
- Unit tests for `Vehicle::currentOdometer()` and related methods
- Tests for validation rules (especially odometer regression)
- Tests for cascade deletes
- Tests for settings endpoints (locale, color, theme)
- Integration tests for report generation
- No tests for error cases (database failures, malformed input)

**Impact:**
- Regressions go unnoticed during refactoring
- Business logic bugs (e.g., odometer calculation) deployed to production
- Confidence in reliability is low

**Fix approach:**
- Add unit tests for model methods (currentOdometer, isOverDue, etc.)
- Add integration tests for cascade deletes to verify no orphaned records
- Add tests for validation edge cases (odometer < previous, invalid dates)
- Target 80%+ coverage for core business logic

---

## No Type Hints on Resource Properties

**Issue:** Resource classes use PHPDoc `@property` to hint model types, but PHP 8.3 could use typed properties or constructor injection for better IDE support and static analysis.

**Files:** All `app/Http/Resources/*.php`

**Pattern:**
```php
/**
 * @property VehicleServiceReminder $resource
 */
class VehicleServiceReminderResource extends JsonResource
```

**Impact:**
- IDE autocomplete is limited
- PHPStan/Psalm cannot verify property access is safe
- Harder for new developers to understand what `$this->resource` is

**Fix approach:**
- Add typed constructor property promotion (if JsonResource supports it)
- Or explicitly type `$resource` in class
- Use strict type checking in CI

---

## No CSRF Protection Middleware Explicitly Enabled

**Issue:** CSRF protection is not explicitly configured in the middleware stack. While Laravel web middleware should include it by default, it's not visible in the code.

**Files:** `bootstrap/app.php` (no explicit CSRF middleware in the middleware stack)

**Impact:**
- If default CSRF middleware is somehow disabled, forms are vulnerable
- Not clear from code that CSRF is protected

**Fix approach:**
- Verify `VerifyCsrfToken` middleware is enabled in `bootstrap/app.php`
- Document the assumption in a comment
- Ensure all forms include `@csrf` in Vue templates

---

## No Deployment Documentation

**Issue:** No documented production deployment process, environment variables, or infrastructure setup.

**Files:** `CLAUDE.md` covers local development but not production

**Impact:**
- Unsafe to deploy (missing config steps could be forgotten)
- No runbook for troubleshooting production issues
- NativePHP mobile build process not documented

**Fix approach:**
- Create `DEPLOYMENT.md` with:
  - Environment variable checklist
  - Database migration steps
  - NativePHP build instructions for iOS/Android
  - Monitoring and alerting setup
  - Backup and recovery procedures

---

## Database Connection Pooling Not Configured

**Issue:** No connection pooling configuration for production use. SQLite default for local development is fine, but production needs pooling.

**Files:** `config/database.php` (standard Laravel setup)

**Impact:**
- Database connection exhaustion under concurrent load
- Slow response times when connection pool is exhausted
- NativePHP mobile app may create many simultaneous requests

**Fix approach:**
- Configure `min_idle_connections`, `max_pool_size` in the database driver
- Use PgBouncer or ProxySQL if using PostgreSQL/MySQL
- Load test before production to find optimal pool size
