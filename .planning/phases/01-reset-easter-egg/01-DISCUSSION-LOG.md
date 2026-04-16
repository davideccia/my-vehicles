# Phase 1: Reset Easter Egg - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-04-16
**Phase:** 01-reset-easter-egg
**Areas discussed:** Click timing window, Reset modal copy, Reset placement in Settings

---

## Click Timing Window

| Option | Description | Selected |
|--------|-------------|----------|
| 2-second window | Each click must follow the previous within 2s; counter resets if too slow | ✓ |
| No time limit | Any 5 clicks ever trigger the modal | |
| Custom window | User-specified timeout | |

**User's choice:** 2-second window
**Notes:** Standard easter egg pattern — prevents accidental triggering from casual repeated visits to Settings.

---

## Reset Modal Copy

| Option | Description | Selected |
|--------|-------------|----------|
| Warning / alarming | Destructive action clearly communicated, red confirm button | ✓ |
| Neutral / informational | Matter-of-fact phrasing | |
| User provides exact text | User writes English strings | |

**User's choice:** Warning / alarming

---

## Reset Label / Framing

| Option | Description | Selected |
|--------|-------------|----------|
| Reset database | reset_db = "Reset database" | |
| Factory reset | reset_db = "Factory reset", reset_db_desc = "Wipe all data and restore defaults." | ✓ |
| Claude decides | Trust Claude's judgment | |

**User's choice:** Factory reset
**Notes:** User preferred the more dramatic "Factory reset" phrasing.

---

## Reset Placement in Settings

| Option | Description | Selected |
|--------|-------------|----------|
| Hidden easter egg only | Accessible only via 5 clicks on h1 — no visible entry point | ✓ |
| Also a visible list item | Factory reset row in Data section | |
| Visible only in dev mode | List item shown when APP_ENV=local | |

**User's choice:** Hidden easter egg only
**Notes:** Keeps it a true developer secret with no visible entry point.

---

## Claude's Discretion

- Exact Italian translations for all 4 new i18n keys
- Exact English copy for `reset_confirm` and `reset_success` (alarming / success tone)
- Internal ref names for reset modal state and loading state

## Deferred Ideas

None — discussion stayed within phase scope.
