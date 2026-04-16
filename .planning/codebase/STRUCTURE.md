# Codebase Structure

**Analysis Date:** 2026-04-16

## Directory Layout

```
my-vehicles/
├── app/                           # Laravel application code (PHP)
│   ├── Concerns/                  # Shared traits/interfaces
│   ├── Enums/                     # Locale enum (It, En)
│   ├── Http/
│   │   ├── Controllers/           # Request handlers (CRUD + Reports + Settings)
│   │   ├── Middleware/            # Inertia, locale, appearance setup
│   │   └── Resources/             # Eloquent resource transformers
│   ├── Models/
│   │   ├── Scopes/                # Default sort scopes for all models
│   │   ├── Vehicle.php            # Aggregate root (UUID, plate, brand, model, year)
│   │   ├── VehicleRefuel.php      # Fuel entries with odometer tracking
│   │   ├── VehicleService.php     # Maintenance records linked to reminders
│   │   ├── VehicleServiceType.php # Maintenance categories (icon, label)
│   │   └── VehicleServiceReminder.php # Odometer-interval maintenance schedules
│   ├── Observers/                 # Model lifecycle hooks
│   ├── Providers/                 # Service container bootstrapping
│   └── Services/                  # Business logic (Report::fuelCosts)
├── bootstrap/                     # App initialization
├── config/                        # Configuration files (database, app, etc.)
├── database/
│   ├── migrations/                # Schema definitions (UUID tables, relationships)
│   ├── factories/                 # Model factories for testing
│   └── seeders/                   # Seed data
├── resources/
│   ├── js/
│   │   ├── actions/               # Auto-generated Wayfinder action structure
│   │   ├── components/            # Reusable Vue components (Vuetify-based)
│   │   ├── composables/           # Vue 3 composables (useAppTheme, useDateFormat, etc.)
│   │   ├── data/                  # Static data files (if any)
│   │   ├── i18n/                  # i18n setup + locale translation files (It, En)
│   │   ├── layouts/               # MobileLayout.vue (bottom nav, theme management)
│   │   ├── pages/                 # Inertia pages (organized by resource)
│   │   │   ├── vehicles/          # CRUD pages (Index, Create, Edit, Form, Show)
│   │   │   ├── vehicle-refuels/   # Refuel management
│   │   │   ├── vehicle-services/  # Service log management
│   │   │   ├── vehicle-service-types/ # Service type management
│   │   │   ├── vehicle-service-reminders/ # Reminder management
│   │   │   ├── Reports.vue        # Fuel cost chart report
│   │   │   └── Settings.vue       # Locale, color, theme settings
│   │   ├── routes/                # Auto-generated Wayfinder route helpers (typed)
│   │   ├── types/                 # TypeScript type definitions (models, UI enums)
│   │   ├── wayfinder/             # Wayfinder route generation utilities
│   │   ├── app.ts                 # Inertia + Vuetify + i18n initialization
│   │   └── composables/           # Shared Vue hooks
│   ├── css/                       # Global stylesheets
│   └── views/                     # Blade template (root app.blade.php)
├── routes/
│   ├── web.php                    # RESTful resource routes (vehicles, refuels, services, etc.)
│   └── console.php                # Artisan commands
├── storage/                       # Application storage (logs, session, uploads)
├── tests/
│   ├── Feature/                   # Integration tests (HTTP, database)
│   └── Unit/                      # Unit tests (models, services)
├── nativephp/                     # NativePHP mobile app (iOS, Android)
│   ├── ios/                       # iOS native wrapper + Xcode project
│   └── android/                   # Android native wrapper + Gradle setup
├── public/                        # Static assets (compiled CSS/JS, images)
├── lang/                          # Laravel localization files (en, it)
├── vendor/                        # Composer dependencies
├── .env                           # Environment variables (local development)
├── artisan                        # Laravel CLI entry point
├── composer.json                  # PHP dependencies
├── package.json                   # Node.js dependencies + scripts
├── vite.config.ts                 # Vite bundler configuration
├── tsconfig.json                  # TypeScript configuration
├── eslint.config.js               # ESLint configuration
├── .prettierrc                    # Prettier formatter config
├── phpunit.xml                    # PHPUnit test configuration
└── .claude/                       # Claude project skills/rules
    └── skills/                    # Project-specific development guides
```

## Directory Purposes

**`app/Http/Controllers/`:**
- Purpose: Handle HTTP requests, validate input, serialize responses
- Contains: Seven RESTful controllers (index, create, store, show, edit, update, destroy)
- Key files:
  - `VehicleController.php` - Main vehicle CRUD
  - `VehicleServiceReminderController.php` - Manage maintenance schedules
  - `ReportController.php` - Generate fuel cost reports
  - `SettingsController.php` - Manage locale, color, theme preferences

**`app/Models/`:**
- Purpose: Define data models, relationships, and domain logic
- Key relationships:
  - `Vehicle` hasMany `VehicleRefuel`, `VehicleService`, `VehicleServiceReminder`
  - `VehicleService` belongsTo `Vehicle`, `VehicleServiceType`, `VehicleServiceReminder`
  - `VehicleServiceReminder` belongsTo `Vehicle`, `VehicleServiceType`
- Scoped by default: All models use `#[ScopedBy(...)]` attribute for automatic ordering

**`app/Http/Resources/`:**
- Purpose: Transform Eloquent models into consistent JSON shapes for frontend
- Pattern: Each resource's `toArray()` method shapes data
- Includes computed properties: `full_name`, `current_odometer`, `is_overdue`

**`app/Http/Middleware/`:**
- Purpose: Process requests before they reach controllers
- `HandleInertiaRequests` - Shares locale, colors, flash messages to frontend
- `HandleLocale` - Reads locale cookie and sets Laravel app locale
- `HandleAppearance` - Persists theme preferences

**`resources/js/pages/`:**
- Purpose: Define Inertia page components (one per route)
- Organized by resource: Each resource (vehicles, refuels, services, etc.) has own subdirectory
- Pattern: Index, Create, Edit, Show pages + shared Form component
- Example: `vehicles/Index.vue`, `vehicles/Form.vue` (shared), `vehicles/Create.vue`, `vehicles/Edit.vue`

**`resources/js/components/`:**
- Purpose: Reusable Vue components
- Built with Vuetify 4 (Material Design 3)
- Examples: DatePickerField, buttons, form inputs

**`resources/js/composables/`:**
- Purpose: Reusable Vue 3 composition API logic
- `useAppTheme()` - Apply Vuetify theme from Inertia props
- `useCurrentUrl()` - Check if URL is current/parent (for active nav state)
- `useDateFormat()` - Locale-aware date formatting
- `useInitials()` - Extract initials from strings

**`resources/js/routes/`:**
- Purpose: Type-safe, auto-generated route helpers
- Auto-generated by Wayfinder from backend routes
- Pattern: Each route file exports functions: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
- Example: `routes/vehicles/index.ts` exports `index`, `store`, `update`, `destroy` functions with `.url()` and `.form()` methods

**`resources/js/types/`:**
- Purpose: TypeScript type definitions matching backend models
- `models.ts` - Vehicle, VehicleRefuel, VehicleService, etc. types
- `ui.ts`, `navigation.ts` - UI-specific types

**`database/migrations/`:**
- Purpose: Define database schema evolution
- All models use UUID primary keys
- Foreign key constraints link vehicles to refuels, services, reminders

**`tests/`:**
- Purpose: Unit and integration tests
- Feature tests exercise full request lifecycle
- Unit tests focus on models and services

**`nativephp/`:**
- Purpose: iOS and Android native app wrappers
- Portrait-only mobile layout
- Loads Laravel backend as embedded webview

## Key File Locations

**Entry Points:**
- `routes/web.php` - All HTTP routes (RESTful resources + settings endpoints)
- `resources/js/app.ts` - Frontend initialization (Inertia, Vuetify, i18n setup)
- `resources/views/app.blade.php` - Root Blade template (loads Inertia app)

**Configuration:**
- `.env` - Environment variables (database, app name, etc.)
- `config/app.php` - App configuration (name, locale, timezone)
- `config/database.php` - Database connection settings
- `config/nativephp.php` - NativePHP mobile app configuration

**Core Logic:**
- `app/Models/Vehicle.php` - Central aggregate with relationships
- `app/Models/VehicleServiceReminder.php` - Business logic for maintenance scheduling
- `app/Services/Report.php` - Report generation service
- `app/Http/Controllers/VehicleController.php` - Main CRUD controller

**Testing:**
- `phpunit.xml` - PHPUnit configuration
- `tests/Feature/` - Feature tests (HTTP endpoints, database operations)
- `tests/Unit/` - Unit tests (model methods, services)

## Naming Conventions

**Files:**
- Controllers: `{Resource}Controller.php` (e.g., `VehicleController.php`)
- Models: `{Entity}.php` (e.g., `Vehicle.php`, `VehicleServiceReminder.php`)
- Resources: `{Entity}Resource.php` (e.g., `VehicleResource.php`)
- Middleware: `Handle{Concern}.php` (e.g., `HandleInertiaRequests.php`)
- Vue components: `{Name}.vue` (e.g., `MobileLayout.vue`, `DatePickerField.vue`)

**Directories:**
- Feature-organized in frontend: `resources/js/pages/{resource}/` (vehicles, vehicle-refuels, etc.)
- Type-organized in backend: `app/Http/{Controllers,Resources,Middleware}/`

## Where to Add New Code

**New Vehicle Management Feature:**
- Primary code: `app/Http/Controllers/VehicleController.php` (add method)
- Tests: `tests/Feature/VehicleControllerTest.php`
- Frontend: `resources/js/pages/vehicles/{NewPage}.vue`

**New Component/Module (e.g., new resource type):**
- Implementation:
  - Model: `app/Models/{NewEntity}.php` (with relationships, UUID, scopes)
  - Controller: `app/Http/Controllers/{NewEntity}Controller.php` (7 CRUD methods)
  - Resource: `app/Http/Resources/{NewEntity}Resource.php`
  - Migration: `database/migrations/{timestamp}_create_{table}_table.php`
  - Pages: `resources/js/pages/{new-entity}/Index.vue`, `Form.vue`, `Create.vue`, `Edit.vue`
  - Routes: Auto-generated via Wayfinder after running `php artisan wayfinder:generate`

**Utilities/Helpers:**
- Shared Vue logic: `resources/js/composables/use{Feature}.ts`
- Shared Vue components: `resources/js/components/{Feature}.vue` (organized by feature)
- Business logic: `app/Services/{Service}.php` (static methods or injectable services)

## Special Directories

**`nativephp/`:**
- Purpose: Native iOS and Android wrappers
- Generated: Automatically via NativePHP CLI
- Committed: Yes (binary files, native configs)
- Do not edit directly; configure via `config/nativephp.php`

**`storage/`:**
- Purpose: Runtime storage (logs, sessions, cache)
- Generated: Yes (created during app initialization)
- Committed: No (includes `.gitignore`)

**`bootstrap/cache/`:**
- Purpose: Compiled configuration and service caches
- Generated: Yes (via `php artisan config:cache`, etc.)
- Committed: No

**`public/build/`:**
- Purpose: Compiled CSS/JS assets from Vite
- Generated: Yes (via `npm run build`)
- Committed: No

**`vendor/`:**
- Purpose: Composer and npm dependencies
- Generated: Yes (via `composer install`, `npm install`)
- Committed: No

---

*Structure analysis: 2026-04-16*
