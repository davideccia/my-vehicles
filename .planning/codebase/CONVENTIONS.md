# Coding Conventions

**Analysis Date:** 2025-04-16

## Naming Patterns

**Files (PHP):**
- Controllers: `PascalCase` with `Controller` suffix (e.g., `VehicleController.php`)
- Models: `PascalCase` (e.g., `Vehicle.php`, `VehicleRefuel.php`)
- Enums: `PascalCase` (e.g., `Locale.php`)
- Observers: `PascalCase` with `Observer` suffix (e.g., `VehicleObserver.php`)
- Scopes: `PascalCase` with `Scope` suffix (e.g., `VehicleDefaultSortScope.php`)
- Resources: `PascalCase` with `Resource` suffix (e.g., `VehicleResource.php`)

**Files (TypeScript/Vue):**
- Vue components: `PascalCase` (e.g., `DatePickerField.vue`, `ConfirmDialog.vue`)
- Composables: `camelCase` with `use` prefix (e.g., `useDateFormat.ts`, `useAppTheme.ts`)
- Type definitions: `*.d.ts` or inline in files
- Routes: kebab-case in URLs (e.g., `/vehicle-refuels`)

**Functions/Methods:**
- camelCase for all functions and methods
- Boolean methods use `is`, `has`, `should`, or `can` prefixes (e.g., `isEditing`, `isOverDue`)
- Model computed properties use lowercase with underscores (e.g., `full_name`, `current_odometer`)

**Variables:**
- camelCase for all variables and properties
- Singular names for single items, plural for collections (e.g., `vehicle`, `vehicles`)

**Types/Interfaces:**
- PascalCase for all TypeScript types and interfaces (e.g., `Vehicle`, `VehicleRefuel`)
- Type names match model names where possible

**Constants:**
- UPPER_SNAKE_CASE for PHP constants
- camelCase for exported JS/TS constants (e.g., `DEFAULT_PRIMARY`, `DEFAULT_SCHEME`)

## Code Style

**Formatting:**
- **Tool**: Prettier (JS/TS/Vue) and Laravel Pint (PHP)
- **Prettier Config** (`/.prettierrc`):
  - Print width: 80 characters
  - Tab width: 4 spaces
  - Single quotes: true
  - Semicolons: true
  - Tailwind CSS plugin enabled with custom functions: `clsx`, `cn`, `cva`
  - YAML files: 2-space tab width

**Linting:**
- **Tool**: ESLint (JS/TS/Vue)
- **Config** (`/eslint.config.js`):
  - Plugin: Vue 3 + TypeScript support
  - Ignores: `vendor/`, `node_modules/`, `public/`, `bootstrap/ssr/`, generated routes
  - Key rules:
    - Multi-word component names: disabled (Vue 3 single-file design)
    - `@typescript-eslint/consistent-type-imports`: error (prefer `type` imports)
    - `import/order`: error (organized import grouping: builtin, external, internal, parent, sibling, index)
    - Brace style: `1tbs` (one true brace style)
    - Blank lines around control statements (`if`, `return`, `for`, `while`, `switch`, `try`)
    - No explicit `any` without justification (warning only, can be overridden)

**PHP Style:**
- Laravel Pint preset: `laravel` (follows PSR-12)
- Strict types: `declare(strict_types=1);` used throughout
- Return types and parameter types: always specified
- Typed properties: all model and class properties typed
- Case sensitivity: enforced in imports

## Import Organization

**Order (TypeScript/Vue):**
1. Builtin/Node modules (`vue`, `@inertiajs/vue3`)
2. External packages (`chart.js`, `vuetify`)
3. Internal modules (aliased with `@/`)
4. Type imports (separate via `type` keyword)

**Path Aliases:**
- `@/*`: maps to `./resources/js/*`
- Used throughout Vue components and composables
- Examples:
  - `@/components/...` for Vue components
  - `@/composables/...` for custom hooks
  - `@/routes/...` for Wayfinder-generated route helpers
  - `@/types/...` for TypeScript type definitions

## Error Handling

**Patterns:**
- Laravel validation via `$request->validate([...])` in controllers — returns redirect with `$errors` on failure
- Form validation errors bound to `form.errors` in Inertia/Vue components
- Assertions in tests: `assertSessionHasErrors()`, `assertDatabaseHas()`, `assertDatabaseMissing()`
- Null coalescing operator `??` for safe defaults
- Optional chaining `?.` in computed properties and methods

## Logging

**Framework:** Laravel built-in logging (not visible in codebase, defaults to stack driver)

**Patterns:**
- No explicit logging seen in application code
- Tests use `withoutVite()` to suppress Vite dev server logs in tests
- Session flash messages via `with('success', '...')` for user-facing feedback

## Comments

**When to Comment:**
- JSDoc/TSDoc not heavily used
- Model computed attribute method uses arrow function syntax for brevity
- Complex color conversion logic in `useAppTheme.ts` has inline comments for hex→HSL→hex operations
- Mostly self-documenting code with clear naming

## Function Design

**Size:** Functions typically 10-30 lines
- Controller methods: 5-15 lines
- Composable functions: 5-20 lines
- Helper utilities: under 50 lines

**Parameters:**
- Form components receive optional model prop: `vehicle?: Vehicle`
- Routes use Wayfinder helpers: `destroy.url(vehicle)`, `update.url({ vehicle_refuel: id })`
- Vue `useForm()` initialization: pass model data or empty defaults

**Return Values:**
- Controllers return `Response` (Inertia) or `RedirectResponse`
- Composables return object with multiple methods: `{ applyPrimaryColor, applyColorScheme }`
- Computed properties use `Attribute` class in Laravel: return `Attribute::make(get: fn() => ...)`

## Module Design

**Exports (PHP):**
- All models in `app/Models/` directory
- All controllers in `app/Http/Controllers/`
- All resources in `app/Http/Resources/`
- Observers in `app/Observers/`
- Scopes in `app/Models/Scopes/`

**Exports (TypeScript/Vue):**
- Composables export named functions (not default)
- Types exported from `resources/js/types/models.ts` and `types/index.ts`
- Routes auto-generated by Wayfinder from controller names
- Components use `<script setup>` syntax

**Barrel Files:**
- `resources/js/types/index.ts` re-exports all types
- No explicit barrel files for components or composables
- Routes auto-generated in `resources/js/routes/` and `resources/js/actions/`

## CRUD Form Pattern (Frontend)

Every resource follows a three-file pattern:

**Form.vue:**
- Receives optional model prop (`vehicle?: Vehicle`)
- Computes `isEditing = computed(() => !!props.vehicle)`
- Initializes `useForm()` with model values or empty defaults
- `submit()` method branches on `isEditing.value`:
  - Editing: `form.put(update.url(id))`
  - Creating: `form.post(store.url())`
- Title and button text i18n keys switch on `isEditing.value`
- Always uses `t('common.save')` and `t('common.cancel')` for button labels

**Create.vue:**
- Thin wrapper page component
- Renders `<Form/>` without model prop

**Edit.vue:**
- Thin wrapper page component
- Renders `<Form :model="model"/>` with model prop

**Examples:**
- `resources/js/pages/vehicle-refuels/Form.vue`
- `resources/js/pages/vehicles/Index.vue`

## API Validation

**Request Validation (PHP):**
- Inline in controller methods via `$request->validate([...])`
- Rules include: `required`, `unique`, `exists`, `min`, `max`, `date`, `nullable`, `integer`
- Example from `VehicleController::store()`:
  ```php
  $validated = $request->validate([
      'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
      'brand' => ['required', 'string', 'max:100'],
      'year' => ['required', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
      'purchase_date' => ['nullable', 'date'],
  ]);
  ```

**Form Handling (Vue/TypeScript):**
- `useForm()` from Inertia automatically binds validation errors to `form.errors`
- Template uses `:error-messages="form.errors.field"` on Vuetify components
- Watch functions trigger automatic calculations (e.g., `total_price = liters * unit_price`)

## Naming Special Cases

**Database columns:**
- snake_case for all database columns (e.g., `plate_number`, `vehicle_id`, `total_price`)
- Auto-cast to date via `protected function casts(): array` in models

**Inertia props (passed to Vue):**
- snake_case to match database (e.g., `vehicle_id`, `total_price`)
- Camelcase computed attributes in resources (e.g., `fullName`, `currentOdometer`)

**Computed Attributes (Laravel):**
- Methods return `Attribute` objects with get-only accessors
- Example: `Vehicle::fullName()` computes on-the-fly display name

**Default Sort Scopes:**
- Applied via PHP 8 attributes `#[ScopedBy(...)]`
- Example: `#[ScopedBy(VehicleDefaultSortScope::class)]` in Vehicle.php
- No explicit ordering calls needed in controllers

**Model Relationships:**
- Singular relationship names for single records: `latestVehicleService()`, `latestVehicleRefuel()`
- Plural for collections: `vehicleRefuels()`, `vehicleServices()`
- Inertia resource mapping flattens nested data automatically

---

*Convention analysis: 2025-04-16*
