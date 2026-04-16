# Roadmap: Settings Reset Easter Egg

## Phase 1: Reset Easter Egg

**Goal:** Implement the full 5-click settings reset easter egg — frontend trigger, confirmation modal, backend reset endpoint, and i18n strings.

**Status:** not started
**Plans:** 2 plans

Plans:
- [x] 01-01-PLAN.md — Backend route, controller method, i18n strings, Wayfinder generation, and feature test
- [ ] 01-02-PLAN.md — Frontend 5-click easter egg trigger, confirmation modal, and reset flow in Settings.vue

### Tasks

1. Add i18n strings (`reset_db`, `reset_db_desc`, `reset_confirm`, `reset_success`) to `en.ts` and `it.ts`
2. Add `resetDb` POST route to `routes/web.php` pointing to `SettingsController@resetDb`
3. Implement `SettingsController::resetDb()` — truncate all app tables, run `DatabaseSeeder`, redirect with flash
4. Regenerate Wayfinder types: `php artisan wayfinder:generate`
5. Update `Settings.vue` — add click counter ref, `onTitleClick` handler, reset modal state, `confirmReset` method, import new route
6. Add reset confirmation dialog and loading state to `Settings.vue` template

### Success Criteria

- [ ] 5 clicks on the Settings title opens the reset confirmation modal
- [ ] Cancel dismisses without side effects
- [ ] Confirm triggers backend reset; success snackbar appears
- [ ] Backend truncates all tables and runs full seeder
- [ ] Both EN and IT strings render correctly
- [ ] `php artisan test --compact` passes
- [ ] `npm run types:check` passes
