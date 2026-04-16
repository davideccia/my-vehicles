# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This App Is

A mobile-first vehicle management app (personal vehicle records: plate, brand, model, year, purchase date). Built with
Laravel 13 + Inertia v3 + Vue 3 as a **NativePHP Mobile** app for iOS/Android. Supports Italian and English locales.

## Commands

```bash
# Development
composer run dev          # Start server, queue, logs, and Vite concurrently
composer run setup        # First-time setup: install deps, key:generate, migrate, build

# Testing
php artisan test --compact                          # All tests
php artisan test --compact tests/Feature/Foo.php   # Specific file
php artisan test --compact --filter=testName        # Specific test

# Code quality
composer run lint          # Run Pint formatter (PHP)
composer run lint:check    # Check PHP formatting without fixing
npm run lint               # ESLint (JS/TS/Vue)
npm run format             # Prettier
npm run types:check        # vue-tsc type checking
composer run ci:check      # Full CI: lint + format + types + tests

# After PHP file changes
vendor/bin/pint --dirty --format agent

# After route/controller changes
php artisan wayfinder:generate    # Regenerate typed Wayfinder route functions

# Frontend build
npm run build              # Production build
npm run dev                # Vite dev server only
```

## Architecture

### Backend

- **Models**: `Vehicle` (UUID PK, `plate_number` unique + auto-uppercased via `VehicleObserver`), `VehicleRefuel`,
  `VehicleService`, `VehicleServiceType`, `VehicleServiceReminder`
- **Relationships**: `Vehicle` hasMany `VehicleRefuel`, `VehicleService`, `VehicleServiceReminder`; `VehicleService` and
  `VehicleServiceReminder` belongsTo `VehicleServiceType`; `VehicleServiceReminder` has a `latestService` relation (
  single record via `services()->orderByDesc('date')->one()`)
- **Controllers**: `VehicleController`, `VehicleRefuelController`, `VehicleServiceController`,
  `VehicleServiceTypeController`, `VehicleServiceReminderController` (all full CRUD), `SettingsController` (locale via
  cookie)
- **Enums**: `App\Enums\Locale` (It, En) used for locale validation
- All routes in `routes/web.php`; root `/` redirects to `/vehicles`

### Frontend

- Pages live in `resources/js/pages/` — `vehicles/`, `vehicle-refuels/`, `vehicle-services/`, `vehicle-service-types/`,
  `vehicle-service-reminders/` (each with Index, Create, Edit, Form) and `Settings.vue`
- Single layout: `resources/js/layouts/MobileLayout.vue` — fixed bottom nav (Vehicles, Refuels, Services, Settings)
- i18n via `resources/js/i18n/index.ts` (Italian default, English fallback)
- UI components from **Vuetify 4** (Material Design 3) — check for existing Vuetify components before creating new ones
- Icons from **Material Design Icons** (`@mdi/font`) — use `mdi-*` icon names
- Route calls use **Wayfinder** — import from `@/actions/` (controllers) or `@/routes/` (named routes), never hardcode
  URLs

### CRUD Form Pattern

Each resource uses a `Form.vue` component shared by `Create.vue` and `Edit.vue`:

- `Form.vue` — contains all form logic; the model prop is **optional** (`vehicle?: Vehicle`)
    - `const isEditing = computed(() => !!props.<model>)`
    - `useForm()` initialises with model values when present, empty defaults otherwise
    - `submit()` calls `.post(store.url())` or `.put(update.url(id))` based on `isEditing`
    - Title uses `t('xxx.add')` or `t('xxx.edit')` based on `isEditing`
    - Buttons always use `t('common.save')` and `t('common.cancel')`
- `Create.vue` — thin Inertia page wrapper, renders `<Form>` without the model prop
- `Edit.vue` — thin Inertia page wrapper, renders `<Form :model="model">` with the model prop

### NativePHP Mobile

- Config in `config/nativephp.php`
- Portrait-only, iOS + Android targets
- Hot reload watches `app/`, `resources/`, `routes/`, `config/`, `public/`
- Runtime mode: persistent (fast ~5-30ms requests)

### Global Scopes

Every model has a default sort scope applied via PHP 8 `#[ScopedBy(...)]` attribute (e.g. `VehicleDefaultSortScope`). These are applied automatically — no need to add `->orderBy()` in controllers.

### API Resources

All models have corresponding Eloquent resources in `app/Http/Resources/` (`VehicleResource`, `VehicleRefuelResource`, etc.). Controllers return these for consistent JSON shape.

### Reports & Services

- `App\Services\Report::fuelCosts()` — static method returning Chart.js-compatible `labels`/`datasets` array for fuel cost over time, optionally filtered by vehicle
- `colority()` helper generates random hex colors for chart datasets
- `ReportController` renders `Reports.vue` with date-range and vehicle filter props

### Settings

Three settings stored as cookies via `SettingsController`:
- **Locale**: `it` / `en` — cookie read in `HandleLocale` middleware, shared to frontend as `page.props.locale`
- **Primary color**: hex string, shared as `page.props.primaryColor`
- **Color scheme**: `light` / `dark` / `system`, shared as `page.props.colorScheme`

`MobileLayout.vue` watches all three props and applies them via `useAppTheme` composable.

### Composables

- `useCurrentUrl` — checks if a URL is the current or a parent URL (used for bottom nav active state)
- `useDateFormat` — locale-aware date formatting
- `useInitials` — extracts initials from a string
- `useAppTheme` — applies Vuetify primary color and color scheme from page props

### Key Model Details

- `Vehicle::$full_name` — computed attribute: `[PLATE] Brand Model`
- `Vehicle::currentOdometer()` — max odometer across latest service and latest refuel
- `VehicleServiceReminder::isOverDue()` — true when `overdueOdometerDiff() >= every`
- `VehicleServiceReminder::overdueOdometerDiff()` — vehicle's current odometer minus latest service odometer for that service type
