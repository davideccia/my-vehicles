## §G — Goal

Add nullable `from`/`to` date filters to refuels and services index pages. Filter on `date` column. Both clearable.

---

## §C — Constraints

- Both `from` and `to` nullable; no filter applied when null
- Clearable via Vuetify `clearable` prop
- Filter params passed as query string alongside existing `vehicle_id` and `page`
- `from` <= `to` not enforced server-side (UI handles UX)
- `date` format: `Y-m-d`
- Controllers validate with `nullable|date`
- Pagination resets to page 1 on filter change
- Inertia `preserveState: true, replace: true` on filter change
- i18n keys added for both `it` and `en` locales
- No new dependencies

---

## §I — Interfaces

- `VehicleRefuelController::index` — accepts `from`, `to` query params
- `VehicleServiceController::index` — accepts `from`, `to` query params
- `vehicle-refuels/Index.vue` — renders two date pickers for `from`/`to`
- `vehicle-services/Index.vue` — renders two date pickers for `from`/`to`

---

## §V — Invariants

V1. `from` filter applies `whereDate('date', '>=', $from)` when present  
V2. `to` filter applies `whereDate('date', '<=', $to)` when present  
V3. Changing `from`/`to` resets `page` to 1 in router call  
V4. Controller passes `selectedFrom` and `selectedTo` back as Inertia props  
V5. Clearing either date input removes the param from query string (`undefined` not `null`)  
V6. Existing `vehicle_id` filter preserved when `from`/`to` change, and vice versa  

---

## §T — Tasks

| id | status | task | cites |
|----|--------|------|-------|
| T1 | x | `VehicleRefuelController::index` — add `from`/`to` filter + props | V1,V2,V4 |
| T2 | x | `VehicleServiceController::index` — add `from`/`to` filter + props | V1,V2,V4 |
| T3 | x | `vehicle-refuels/Index.vue` — add date picker fields + `onDateFilter` | V3,V5,V6 |
| T4 | x | `vehicle-services/Index.vue` — add date picker fields + `onDateFilter` | V3,V5,V6 |
| T5 | x | i18n keys `refuels.from`, `refuels.to`, `services.from`, `services.to` | §I |
| T6 | x | Feature tests: refuel index filters by `from`/`to` | V1,V2 |
| T7 | x | Feature tests: service index filters by `from`/`to` | V1,V2 |

---

## §B — Bug Log

| id | date | cause | fix |
|----|------|-------|-----|
