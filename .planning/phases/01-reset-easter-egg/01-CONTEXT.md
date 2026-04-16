# Phase 1: Reset Easter Egg - Context

**Gathered:** 2026-04-16
**Status:** Ready for planning

<domain>
## Phase Boundary

Add a hidden 5-click easter egg on the Settings page `h1` title that triggers a factory reset confirmation modal. Confirming truncates all app tables and reruns the full DatabaseSeeder via `Artisan::call('db:seed')`. Purely a developer tool — no visible entry point, no environment gating.

</domain>

<decisions>
## Implementation Decisions

### Click Trigger
- **D-01:** 5 consecutive clicks on the Settings `h1` title open the reset confirmation modal.
- **D-02:** Clicks must happen within a **2-second window** — each click must follow the previous within 2s. If the user is too slow, the counter resets to 0. This prevents accidental triggering from casual repeated visits.
- **D-03:** No visual indication of the counter state (hidden easter egg — counter is invisible).
- **D-04:** Counter resets after the modal opens, regardless of whether the user cancels or confirms.

### Modal & UX
- **D-05:** Purely a **hidden easter egg** — no visible list item in the Data section. Access is exclusively via the h1 click sequence.
- **D-06:** Modal follows the existing `v-dialog` + `v-card rounded="xl"` pattern already used in Settings.vue for the import confirmation dialog.
- **D-07:** Tone is **warning / alarming** — the modal copy must make clear this action is destructive and cannot be undone.
- **D-08:** Confirm button uses `color="error"` + `variant="tonal"` (same as the import confirm button pattern).
- **D-09:** Loading state on the Confirm button while the request is in flight (mirrors `importLoading` pattern).
- **D-10:** Success snackbar on completion; error snackbar on failure — reuse existing `snackbar`/`snackbarMessage`/`snackbarColor` refs and the flash-watcher pattern.

### i18n Strings
- **D-11:** All keys under `settings.*` namespace in both `en.ts` and `it.ts`.
- **D-12:** Required keys and English values:
  - `reset_db` → `"Factory reset"`
  - `reset_db_desc` → `"Wipe all data and restore defaults."`
  - `reset_confirm` → warning-toned, e.g. `"This will delete ALL data and restore the default seed. This cannot be undone."`
  - `reset_success` → Claude decides (e.g. `"Database reset successfully."`)

### Backend
- **D-13:** New POST route `settings.reset` → `SettingsController@resetDb` in `routes/web.php`.
- **D-14:** Truncate all 5 app tables in dependency order (service_reminders → services → refuels → vehicles → service_types). Use `DB::table()->delete()` with `PRAGMA foreign_keys = OFF` (consistent with existing `import` method in SettingsController).
- **D-15:** Run full seeder via `Artisan::call('db:seed')` after truncation.
- **D-16:** Return `redirect()->back()->with('success', 'settings.reset_success')`.
- **D-17:** Wayfinder types regenerated after adding the route (`php artisan wayfinder:generate`).

### Claude's Discretion
- Exact Italian translations for the 4 new i18n keys
- Exact `reset_confirm` and `reset_success` English copy (tone: alarming / success respectively)
- Ref name for the new reset modal state (e.g. `showResetConfirm`)
- Ref name for reset loading state (e.g. `resetLoading`)

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

No external specs — requirements fully captured in decisions above and the files below.

### Core files to read before implementing
- `resources/js/pages/Settings.vue` — existing dialog, snackbar, and loading patterns to follow exactly
- `app/Http/Controllers/SettingsController.php` — existing controller; `import()` method is the pattern for truncation
- `resources/js/i18n/locales/en.ts` — add new keys under `settings.*`
- `resources/js/i18n/locales/it.ts` — add Italian translations
- `routes/web.php` — add POST route before running wayfinder:generate
- `.planning/REQUIREMENTS.md` — full acceptance criteria

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `v-dialog` + `v-card rounded="xl"` pattern: already used in Settings.vue (lines 225–234) for import confirmation — reset modal should look identical in structure
- `snackbar`/`snackbarMessage`/`snackbarColor` refs: already declared — add reset flow without new refs
- `flashSuccess` computed + watcher: already handles `page.props.flash?.success` — backend flash key `settings.reset_success` will be picked up automatically
- `importLoading` ref pattern: mirror with `resetLoading` ref for Confirm button loading state

### Established Patterns
- Backend truncation: `DB::statement('PRAGMA foreign_keys = OFF')` + `DB::table()->delete()` in dependency order, then re-enable — match exactly from `import()` method
- Flash-driven snackbar: controller returns `->with('success', 'translation.key')`, frontend watcher translates via `t(val)` — works without changes
- Route registration: named routes follow `settings.*` convention

### Integration Points
- `routes/web.php`: add `Route::post('/settings/reset', [SettingsController::class, 'resetDb'])->name('settings.reset');`
- `@/routes/settings`: Wayfinder-generated file — regenerate after route addition, then import `resetDb` in Settings.vue

</code_context>

<specifics>
## Specific Ideas

- The "Factory reset" framing (not "Reset database") — user preferred the more dramatic phrasing
- Warning tone is intentional — modal should feel like a serious warning, not a casual confirmation

</specifics>

<deferred>
## Deferred Ideas

None — discussion stayed within phase scope.

</deferred>

---

*Phase: 01-reset-easter-egg*
*Context gathered: 2026-04-16*
