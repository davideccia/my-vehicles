---
phase: 01-reset-easter-egg
plan: 02
subsystem: frontend
tags: [settings, reset, easter-egg, vuetify, inertia, i18n]
dependency_graph:
  requires: [POST /settings/reset endpoint, i18n reset keys, Wayfinder reset route]
  provides: [5-click easter egg trigger, reset confirmation modal, confirmReset method]
  affects: [resources/js/pages/Settings.vue]
tech_stack:
  added: []
  patterns: [click counter with setTimeout window, v-dialog confirmation pattern, Inertia router.post with loading state]
key_files:
  created: []
  modified:
    - resources/js/pages/Settings.vue
decisions:
  - "Imported Wayfinder export as 'reset as resetDb' since Wayfinder names by route (settings.reset -> reset) not PHP method (resetDb)"
  - "resetClickTimer declared as module-level let (not ref) since it holds a timer handle not reactive UI state"
  - "Pre-existing type errors in vehicle-services/Form.vue and lint errors in Reports.vue are out of scope — confirmed pre-existing via git stash check"
metrics:
  duration: "~15 minutes"
  completed_date: "2026-04-16"
  tasks_completed: 1
  tasks_total: 2
  files_changed: 1
---

# Phase 01 Plan 02: Settings Easter Egg Frontend Summary

**One-liner:** 5-click hidden trigger on Settings h1 opens factory reset confirmation modal wired to POST /settings/reset with loading state and automatic snackbar feedback.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Add click counter, modal state, and reset methods to Settings.vue | 4c1932c | resources/js/pages/Settings.vue |

## Tasks Pending Human Verification

| Task | Name | Status |
|------|------|--------|
| 2 | Verify easter egg flow end-to-end | CHECKPOINT — awaiting human verification |

## What Was Built

### Click Counter with 2-Second Window (D-01, D-02)

`onTitleClick()` increments `resetClickCount` on each h1 click. A `setTimeout` of 2000ms resets the counter if 5 clicks are not reached within the window. On the 5th click, the counter resets to 0, the timer is cleared, and `showResetConfirm` is set to true, opening the modal.

### Reset Confirmation Modal (D-06, D-07, D-08)

`<v-dialog v-model="showResetConfirm" max-width="360">` follows the exact structure of the existing import confirmation dialog. Cancel button calls `cancelReset()` (closes modal, no side effects). Confirm button calls `confirmReset()` with `color="error" variant="tonal"` and `:loading="resetLoading"` for the spinner during the POST.

### confirmReset Method (D-09, D-10)

Closes the modal, sets `resetLoading = true`, posts to `resetDb.url()` (`/settings/reset`) with empty body. On finish, clears `resetLoading`. The existing `flashSuccess` watcher picks up the `settings.reset_success` flash key automatically — no additional snackbar code required.

### Hidden Easter Egg (D-03, D-05)

The h1 has `@click="onTitleClick"` only. No `cursor: pointer`, no visual counter display, no other hint. The trigger is completely hidden.

## Wayfinder Import Note

Wayfinder generates the export as `reset` (derived from route name `settings.reset`), not `resetDb`. The import uses `reset as resetDb` alias to match the plan's intent and keep the call sites readable (`resetDb.url()`).

## Deviations from Plan

None — plan executed exactly as written.

## Known Stubs

None — all functionality is fully wired. The modal, counter, and POST are all connected end-to-end.

## Pre-Existing Issues (Out of Scope)

The following issues existed before this plan's changes and are deferred:

- `resources/js/pages/vehicle-services/Form.vue` line 62: TypeScript parse errors (`TS1005`, `TS1128`) — inline type annotation in `:items` binding causes `vue-tsc` to fail. Confirmed pre-existing via `git stash` verification.
- `resources/js/pages/Reports.vue` line 35: `formatDate` assigned but never used (`@typescript-eslint/no-unused-vars`).
- `.claude/worktrees/agent-a57e574d/vite.config.ts`: not found by project service (worktree artifact).

## Threat Flags

No new threat surface. The endpoint is intentionally unguarded per plan threat model (T-02-01: single-user NativePHP app, direct POST via devtools is acceptable).

## Checkpoint State

**Task 2 is a `checkpoint:human-verify` gate.** Human verification is required before this plan is marked complete.

### What to Verify

1. Run `composer run dev`
2. Navigate to Settings page
3. Click the "Settings" title 5 times quickly (within 2 seconds between clicks)
4. Verify: confirmation modal appears with warning text
5. Click "Cancel" — verify modal closes, no changes
6. Repeat 5 clicks to reopen
7. Click "Factory reset" (red button) — verify loading spinner appears
8. Verify: success snackbar "Database reset successfully." appears
9. Navigate to Vehicles — verify seed data is present
10. Switch locale to Italian, repeat steps 3-8 — verify Italian strings

### Resume Signal

Type "approved" or describe any issues found.

## Self-Check: PASSED

- resources/js/pages/Settings.vue contains `reset as resetDb` import: FOUND (line 7)
- Settings.vue contains `const resetLoading = ref(false)`: FOUND (line 134)
- Settings.vue contains `const showResetConfirm = ref(false)`: FOUND (line 135)
- Settings.vue contains `const resetClickCount = ref(0)`: FOUND (line 136)
- Settings.vue contains `function onTitleClick(): void`: FOUND (line 139)
- Settings.vue contains `resetClickCount.value >= 5`: FOUND (line 146)
- Settings.vue contains `setTimeout` with `2000`: FOUND (line 154)
- Settings.vue contains `function cancelReset(): void`: FOUND (line 159)
- Settings.vue contains `function confirmReset(): void`: FOUND (line 163)
- Settings.vue contains `router.post(resetDb.url()`: FOUND (line 166)
- Settings.vue contains `@click="onTitleClick"` on h1: FOUND (line 179)
- Settings.vue contains `v-model="showResetConfirm"`: FOUND (line 288)
- Settings.vue contains `:loading="resetLoading"`: FOUND (line 294)
- Settings.vue contains `t('settings.reset_confirm')`: FOUND (line 290)
- Settings.vue contains `t('settings.reset_db')`: FOUND (line 294)
- Settings.vue does NOT contain `cursor: pointer` on h1: CONFIRMED
- Task 1 commit 4c1932c: FOUND
