---
phase: 01-reset-easter-egg
plan: 01
subsystem: backend
tags: [settings, reset, easter-egg, i18n, wayfinder, testing]
dependency_graph:
  requires: []
  provides: [POST /settings/reset endpoint, i18n reset keys, Wayfinder reset route]
  affects: [SettingsController, routes/web.php, en.ts, it.ts, SettingsTest]
tech_stack:
  added: [Artisan facade for db:seed call]
  patterns: [PRAGMA foreign_keys OFF/ON for SQLite truncation, RefreshDatabase in feature tests]
key_files:
  created: []
  modified:
    - routes/web.php
    - app/Http/Controllers/SettingsController.php
    - resources/js/i18n/locales/en.ts
    - resources/js/i18n/locales/it.ts
    - tests/Feature/SettingsTest.php
decisions:
  - "Used Artisan::call('db:seed') matching plan spec — full DatabaseSeeder reruns after truncation"
  - "RefreshDatabase trait added to SettingsTest; existing tests unaffected since they don't mutate DB state"
  - "Wayfinder generates export named 'reset' (route name) not 'resetDb' (method name) — route is importable"
  - "Wayfinder routes are gitignored generated artifacts; regeneration deferred to post-merge wayfinder:generate run"
metrics:
  duration: "~20 minutes"
  completed_date: "2026-04-16"
  tasks_completed: 2
  tasks_total: 2
  files_changed: 5
---

# Phase 01 Plan 01: Backend Reset Endpoint and i18n Strings Summary

**One-liner:** POST /settings/reset endpoint truncating 5 SQLite tables and reseeding via DatabaseSeeder, with factory-reset i18n strings in EN and IT locales.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Add backend route, controller method, and i18n strings | 35dacdb | routes/web.php, SettingsController.php, en.ts, it.ts |
| 2 | Add feature test for resetDb endpoint | acb33a9 | tests/Feature/SettingsTest.php |

## What Was Built

### Backend Endpoint

`POST /settings/reset` registered as `settings.reset` in `routes/web.php`. `SettingsController::resetDb()` follows the existing `import()` truncation pattern exactly:

1. Disables SQLite foreign key checks (`PRAGMA foreign_keys = OFF`)
2. Truncates all 5 app tables in dependency order inside a transaction: `vehicle_service_reminders`, `vehicle_services`, `vehicle_refuels`, `vehicles`, `vehicle_service_types`
3. Re-enables foreign key checks
4. Calls `Artisan::call('db:seed')` to restore default seeded state
5. Returns redirect with flash key `settings.reset_success`

### i18n Strings

4 keys added to both `en.ts` and `it.ts` settings objects:
- `reset_db`: "Factory reset" / "Reset di fabbrica"
- `reset_db_desc`: "Wipe all data and restore defaults." / "Cancella tutti i dati e ripristina i valori predefiniti."
- `reset_confirm`: Full warning text (cannot be undone)
- `reset_success`: "Database reset successfully." / "Database reimpostato con successo."

### Feature Test

`SettingsTest::test_reset_db_truncates_and_reseeds()` verifies:
- Endpoint returns redirect (not error)
- Flash session contains `settings.reset_success`
- Factory-created vehicle is absent after truncation
- Seeder created fresh vehicles and service types

`RefreshDatabase` trait added to isolate all SettingsTest database state. All 12 SettingsTest tests pass.

## Deviations from Plan

None — plan executed exactly as written.

### Notes on Implementation

**Wayfinder route naming:** Wayfinder generates an export named `reset` (derived from the route name `settings.reset`), not `resetDb` (the PHP method name). The route is fully functional and importable as `import { reset } from '@/routes/settings'`. This is expected Wayfinder behavior.

**Wayfinder gitignore:** The `resources/js/routes/` directory is gitignored (generated artifacts). The Wayfinder file will be regenerated automatically on the next `php artisan wayfinder:generate` run after the route changes are merged to main.

**Test execution context:** Tests were validated by temporarily copying worktree files to the main project (which has vendor/artisan available). This is a worktree execution constraint — the worktree has no vendor directory.

## Known Stubs

None — all functionality is fully wired. The endpoint truncates and reseeds correctly as verified by the passing test.

## Threat Flags

No new threat surface beyond what is documented in the plan's threat model. The endpoint is intentionally unguarded per D-13 (single-user NativePHP mobile app; 5-click easter egg is the access control).

## Self-Check: PASSED

- routes/web.php contains `settings.reset` route: FOUND (line 31)
- SettingsController.php contains `resetDb()` method: FOUND
- SettingsController.php contains `Artisan::call('db:seed')`: FOUND
- en.ts contains `reset_db` key: FOUND
- it.ts contains `reset_db` key: FOUND
- tests/Feature/SettingsTest.php contains `test_reset_db_truncates_and_reseeds`: FOUND
- Task 1 commit 35dacdb: FOUND
- Task 2 commit acb33a9: FOUND
- All 12 SettingsTest tests pass: VERIFIED
