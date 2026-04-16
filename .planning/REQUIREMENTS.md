# Requirements: Settings Reset Easter Egg

## Functional Requirements

### FR-1: 5-Click Trigger
- Clicking the Settings page `h1` title 5 times opens the reset confirmation modal
- The click counter resets after the modal opens (regardless of outcome)
- No visual indication of the counter state (hidden easter egg)

### FR-2: Confirmation Modal
- Modal follows the existing `v-dialog` + `v-card rounded="xl"` pattern in Settings.vue
- Displays a warning message explaining the action (truncate DB + seed)
- Has Cancel and Confirm buttons
- Cancel: closes modal, no side effects
- Confirm: calls the backend reset endpoint

### FR-3: Backend Reset Endpoint
- New POST route: `settings.reset` (or equivalent)
- Controller method in `SettingsController`
- Truncates all application tables (vehicles, vehicle_refuels, vehicle_services, vehicle_service_types, vehicle_service_reminders)
- Runs `php artisan db:seed` (full `DatabaseSeeder`)
- Returns Inertia redirect with flash success message
- Always available (no environment guard)

### FR-4: Loading & Feedback
- Confirm button shows loading state while request is in flight
- Success snackbar on completion (reuses existing snackbar pattern)
- Error snackbar if the backend returns an error

### FR-5: Internationalisation
- All new strings in both `en.ts` and `it.ts`
- Keys under `settings.*` namespace
- Required keys: `reset_db`, `reset_db_desc`, `reset_confirm`, `reset_success`

## Non-Functional Requirements

- No new Vue components or PHP classes — extend existing files only
- Follow existing Vuetify 4 + vue-i18n patterns in Settings.vue
- Wayfinder route must be regenerated after adding the new route
- PHP formatted with Pint after changes

## Out of Scope

- Environment-based gating
- Granular table selection
- Undo / backup before reset
- Admin authentication
