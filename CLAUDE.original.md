# CLAUDE.md

Mobile-first vehicle management app (personal vehicle records: plate, brand, model, year, purchase date). Laravel 13 + Inertia v3 + Vue 3 as **NativePHP Mobile** app for iOS/Android. Italian and English locales.

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

- **Models**: `Vehicle` (UUID PK, `plate_number` unique + auto-uppercased via `VehicleObserver`), `VehicleRefuel`, `VehicleService`, `VehicleServiceType`, `VehicleServiceReminder`
- **Relationships**: `Vehicle` hasMany `VehicleRefuel`, `VehicleService`, `VehicleServiceReminder`; `VehicleService` and `VehicleServiceReminder` belongsTo `VehicleServiceType`; `VehicleServiceReminder` has `latestService` relation (single record via `services()->orderByDesc('date')->one()`)
- **Controllers**: `VehicleController`, `VehicleRefuelController`, `VehicleServiceController`, `VehicleServiceTypeController`, `VehicleServiceReminderController` (all full CRUD), `SettingsController` (locale via cookie)
- **Enums**: `App\Enums\Locale` (It, En) for locale validation
- All routes in `routes/web.php`; root `/` redirects to `/vehicles`

### Frontend

- Pages in `resources/js/pages/` — `vehicles/`, `vehicle-refuels/`, `vehicle-services/`, `vehicle-service-types/`, `vehicle-service-reminders/` (each with Index, Create, Edit, Form) and `Settings.vue`
- Single layout: `resources/js/layouts/MobileLayout.vue` — fixed bottom nav (Vehicles, Refuels, Services, Settings)
- i18n via `resources/js/i18n/index.ts` (Italian default, English fallback)
- UI components from **Vuetify 4** (Material Design 3) — check existing Vuetify components before creating new
- Icons from **Material Design Icons** (`@mdi/font`) — use `mdi-*` icon names
- Route calls use **Wayfinder** — import from `@/actions/` (controllers) or `@/routes/` (named routes), never hardcode URLs

### CRUD Form Pattern

Each resource uses `Form.vue` shared by `Create.vue` and `Edit.vue`:

- `Form.vue` — all form logic; model prop **optional** (`vehicle?: Vehicle`)
    - `const isEditing = computed(() => !!props.<model>)`
    - `useForm()` initialises with model values when present, empty defaults otherwise
    - `submit()` calls `.post(store.url())` or `.put(update.url(id))` based on `isEditing`
    - Title uses `t('xxx.add')` or `t('xxx.edit')` based on `isEditing`
    - Buttons always use `t('common.save')` and `t('common.cancel')`
- `Create.vue` — thin Inertia page wrapper, renders `<Form>` without model prop
- `Edit.vue` — thin Inertia page wrapper, renders `<Form :model="model">` with model prop

### NativePHP Mobile

- Config in `config/nativephp.php`
- Portrait-only, iOS + Android targets
- Hot reload watches `app/`, `resources/`, `routes/`, `config/`, `public/`
- Runtime: persistent (fast ~5-30ms requests)

### Global Scopes

Every model has default sort scope via PHP 8 `#[ScopedBy(...)]` attribute (e.g. `VehicleDefaultSortScope`). Applied automatically — no need for `->orderBy()` in controllers.

### API Resources

All models have Eloquent resources in `app/Http/Resources/` (`VehicleResource`, `VehicleRefuelResource`, etc.). Controllers return these for consistent JSON shape.

### Reports & Services

- `App\Services\Report::fuelCosts()` — static method returning Chart.js-compatible `labels`/`datasets` for fuel cost over time, optionally filtered by vehicle
- `colority()` helper generates random hex colors for chart datasets
- `ReportController` renders `Reports.vue` with date-range and vehicle filter props

### Settings

Three settings stored as cookies via `SettingsController`:
- **Locale**: `it` / `en` — cookie read in `HandleLocale` middleware, shared to frontend as `page.props.locale`
- **Primary color**: hex string, shared as `page.props.primaryColor`
- **Color scheme**: `light` / `dark` / `system`, shared as `page.props.colorScheme`

`MobileLayout.vue` watches all three props, applies via `useAppTheme` composable.

### Composables

- `useCurrentUrl` — checks if URL is current or parent URL (bottom nav active state)
- `useDateFormat` — locale-aware date formatting
- `useInitials` — extracts initials from string
- `useAppTheme` — applies Vuetify primary color and color scheme from page props

### Key Model Details

- `Vehicle::$full_name` — computed attribute: `[PLATE] Brand Model`
- `Vehicle::currentOdometer()` — max odometer across latest service and latest refuel
- `VehicleServiceReminder::isOverDue()` — true when `overdueOdometerDiff() >= every`
- `VehicleServiceReminder::overdueOdometerDiff()` — vehicle's current odometer minus latest service odometer for that service type

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

Laravel Boost guidelines curated by Laravel maintainers. Follow closely for best experience building Laravel applications.

## Foundational Context

Laravel application. Main ecosystem packages & versions:

- php - 8.5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA_VUE) - v3
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

Domain-specific skills available. Activate relevant skill when working in that domain — don't wait until stuck.

- `laravel-best-practices` — Apply when writing, reviewing, or refactoring Laravel PHP code: controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, Eloquent queries. Triggers for N+1, query performance, caching, authorization, security, validation, error handling, queue/job config, route definitions, architecture. Also for Laravel code reviews and refactoring. Covers all Laravel backend PHP code patterns.
- `wayfinder-development` — Use for Laravel Wayfinder (auto-generates typed functions for Laravel controllers and routes). ALWAYS use when frontend code needs to call backend routes or controller actions. Triggers: connecting React/Vue/Svelte/Inertia frontend to Laravel controllers/routes, building end-to-end features, wiring forms or links to backend, fixing route-related TypeScript errors, importing from `@/actions` or `@/routes`, running `wayfinder:generate`. Use Wayfinder route functions instead of hardcoded URLs. Covers: `wayfinder()` vite plugin, `.url()/.get()/.post()/.form()`, query params, route model binding, tree-shaking. Not for backend-only tasks.
- `inertia-vue-development` — Develops Inertia.js v3 Vue client-side apps. Activates when creating Vue pages, forms, or navigation; using `<Link>`, `<Form>`, `useForm`, `useHttp`, `setLayoutProps`, or router; working with deferred props, prefetching, optimistic updates, instant visits, or polling; or when user mentions Vue with Inertia, Vue pages, Vue forms, or Vue navigation.
- `nativephp-mobile` — Builds native iOS and Android apps with PHP & Laravel. Activate when using native device APIs (camera, dialog, biometrics, scanner, geolocation, push notifications), EDGE components (bottom-nav, top-bar, side-nav), `#nativephp` JavaScript imports, native mobile events, NativePHP Artisan commands (`native:run`, `native:install`, `native:watch`), deep links, secure storage, or mobile app deployment.

## Conventions

- Follow all existing code conventions. When creating or editing a file, check sibling files for correct structure, approach, and naming.
- Use descriptive names for variables and methods. e.g. `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing new ones.

## Verification Scripts

Don't create verification scripts or tinker when tests cover that functionality. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Don't change application dependencies without approval.

## Frontend Bundling

If user doesn't see a frontend change in UI, they may need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

Only create documentation files if explicitly requested.

## Replies

Be concise — focus on what's important, not obvious details.

=== boost rules ===

# Laravel Boost

## Tools

Laravel Boost is an MCP server with tools designed for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` for read-only DB queries instead of raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve correct scheme, domain, and port for project URLs. Always use before sharing a URL with user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Don't skip. Returns version-specific docs based on installed packages automatically.
- Pass `packages` array to scope results when relevant packages are known.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Most relevant results come first.
- Don't add package names to queries — package info already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via command line (e.g. `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` for parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read config values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files from `config/` directly.
- Check environment variables by reading `.env` directly.

## Tinker

- Execute PHP in app context for debugging and testing. Don't create models without user approval — prefer tests with factories. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Don't leave empty zero-parameter `__construct()` unless constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write or update a test, then run affected tests to verify they pass.
- Run minimum tests needed for quality and speed. Use `php artisan test --compact` with specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check docs before making changes.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add empty state with pulsing or animated skeleton.
- Axios removed. Use built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` — no separate Node.js server needed during development.
- Event renames: `invalid` → `httpException`, `exception` → `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- `future` configuration namespace removed — all v2 future options now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (migrations, controllers, models, etc.). List available commands with `php artisan list`, check parameters with `php artisan [command] --help`.
- For generic PHP classes, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands. Pass correct `--options` for correct behavior.

### Model Creation

When creating new models, also create useful factories and seeders. Ask user if they need other things, using `php artisan make:model --help` for available options.

## APIs & Eloquent Resources

For APIs, default to Eloquent API Resources and API versioning unless existing API routes don't — then follow existing convention.

## URL Generation

When generating links, prefer named routes and `route()` function.

## Testing

- When creating models for tests, use factories. Check for custom factory states before manually setting up model.
- Faker: use methods like `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions for `$this->faker` vs `fake()`.
- Use `php artisan make:test [options] {name}` for feature tests, `--unit` for unit tests. Most tests should be feature tests.

## Vite Error

If you get "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest", run `npm run build` or ask user to run `npm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- After modifying any PHP files, run `vendor/bin/pint --dirty --format agent` before finalizing changes.
- Don't run `vendor/bin/pint --test --format agent`, run `vendor/bin/pint --format agent` to fix formatting issues.

=== phpunit/core rules ===

# PHPUnit

- App uses PHPUnit for testing. All tests must be PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create new tests.
- If you see a Pest test, convert it to PHPUnit.
- After updating a test, run that singular test.
- When feature tests pass, ask user if they want to run entire test suite.
- Tests must cover all happy paths, failure paths, and edge cases.
- Don't remove any tests or test files without approval. These are core to the application.

## Running Tests

- Run minimal tests with appropriate filter before finalizing.
- All tests: `php artisan test --compact`.
- All tests in file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- Filter by test name: `php artisan test --compact --filter=testName` (recommended after changing related file).

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

=== nativephp/mobile rules ===

## NativePHP Mobile

- NativePHP Mobile is Laravel package for building native iOS and Android apps using PHP and native UI components. Runs full PHP runtime on device with SQLite — no web server required.
- Documentation: `https://nativephp.com/docs/mobile/3/**`
- IMPORTANT: Always activate `nativephp-mobile` skill every time you work on any NativePHP functionality.

### Build Commands — Tell the User, Never Run

**CRITICAL: Never execute any of these commands yourself. Always instruct user to run them manually in their terminal.**

| Command | Purpose |
|---|---|
| `npm run build -- --mode=ios` | Build frontend assets for iOS |
| `npm run build -- --mode=android` | Build frontend assets for Android |
| `php artisan native:run ios` | Compile and run on iOS simulator/device |
| `php artisan native:run android` | Compile and run on Android emulator/device |
| `php artisan native:run ios --watch` | Build, deploy, then start hot reload — all in one |
| `php artisan native:watch` | Hot reload (watch for file changes) |
| `php artisan native:open` | Open project in Xcode or Android Studio |

**Always ask which platform before giving any build or run command.** If user hasn't specified iOS or Android, ask: "Which platform do you want to build/test on — iOS or Android?" Never assume platform.

When platform confirmed, give relevant command(s) above and tell user to run in their terminal. Don't run it yourself.
</laravel-boost-guidelines>