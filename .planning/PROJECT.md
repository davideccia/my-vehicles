# Project: Settings Reset Easter Egg

**Type:** Brownfield feature addition  
**Date:** 2026-04-16  
**App:** my-vehicles — NativePHP mobile vehicle management app (Laravel 13 + Inertia v3 + Vue 3 + Vuetify 4)

## Feature Description

Add a developer easter egg to `Settings.vue`: clicking the page title (`h1`) 5 times in quick succession opens a confirmation modal. Confirming triggers a backend endpoint that truncates all database tables and runs `php artisan db:seed` (full `DatabaseSeeder`). This gives a one-tap way to reset to a clean seeded state during development and testing.

## Scope

- **In scope:** 5-click counter on Settings page title, confirmation modal (following existing dialog pattern), backend reset endpoint, i18n strings (IT + EN), Wayfinder route
- **Out of scope:** per-table granularity, auth/authorization guard, environment gating, audit logging

## Technical Context

- Backend: `SettingsController` + new route in `routes/web.php`
- Frontend: `Settings.vue` (script + template only — no new files needed)
- i18n: `resources/js/i18n/locales/en.ts` and `it.ts`
- Route typing: regenerate via `php artisan wayfinder:generate` after adding the route
- Seeder: `php artisan db:seed` (full `DatabaseSeeder`, always available)

## Success Criteria

1. Clicking the Settings title 5 times opens the reset confirmation modal
2. Cancelling dismisses without side effects; click counter resets
3. Confirming calls the backend, shows loading state, then shows success snackbar
4. Backend truncates all app tables and runs the full seeder
5. Counter resets after the modal opens (regardless of outcome)
6. Both IT and EN strings are correct
