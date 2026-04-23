# SPEC.md

## §G — Goal

Move edit/delete buttons from right-side column to card bottom in all 5 `Index.vue` files. More horizontal space for content. Delete button stays red (`color="error"`). Confirm alert logic unchanged.

---

## §C — Constraints

- Files: `vehicles`, `vehicle-refuels`, `vehicle-services`, `vehicle-service-types`, `vehicle-service-reminders` Index.vue
- Remove right-side `div.border-s` column containing buttons
- Add bottom action row (e.g. `v-card-actions` or plain div) with edit + delete buttons side by side
- Edit btn: `variant="outlined"` icon `mdi-pencil`
- Delete btn: `variant="elevated" color="error"` icon `mdi-delete` — must stay red
- `ConfirmDialog`, `promptDelete`, `doDelete` logic untouched

---

## §I — Interfaces

| id | surface | path |
|----|---------|------|
| I.vehicles | vehicles Index | `resources/js/pages/vehicles/Index.vue` |
| I.refuels | vehicle-refuels Index | `resources/js/pages/vehicle-refuels/Index.vue` |
| I.services | vehicle-services Index | `resources/js/pages/vehicle-services/Index.vue` |
| I.types | vehicle-service-types Index | `resources/js/pages/vehicle-service-types/Index.vue` |
| I.reminders | vehicle-service-reminders Index | `resources/js/pages/vehicle-service-reminders/Index.vue` |

---

## §V — Invariants

| id | invariant |
|----|-----------|
| V10 | All 5 Index.vue: edit/delete buttons in card bottom, not right column |
| V11 | Delete button keeps `color="error"` in all files |
| V12 | `ConfirmDialog`, `promptDelete`, `doDelete` logic unchanged in all files |
| V13 | Right-side `border-s` column div removed from all cards |
| V14 | Card content uses full horizontal width after button move |

---

## §T — Tasks

| id | status | goal | cites |
|----|--------|------|-------|
| T10 | x | Move buttons to card bottom in `vehicles/Index.vue` | V10,V11,V12,V13,V14,I.vehicles |
| T11 | x | Move buttons to card bottom in `vehicle-refuels/Index.vue` | V10,V11,V12,V13,V14,I.refuels |
| T12 | x | Move buttons to card bottom in `vehicle-services/Index.vue` | V10,V11,V12,V13,V14,I.services |
| T13 | x | Move buttons to card bottom in `vehicle-service-types/Index.vue` | V10,V11,V12,V13,V14,I.types |
| T14 | x | Move buttons to card bottom in `vehicle-service-reminders/Index.vue` | V10,V11,V12,V13,V14,I.reminders |

---

## §B — Bug Log

| id | date | cause | fix |
|----|------|-------|-----|