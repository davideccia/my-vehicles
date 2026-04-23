# SPEC.md

## §G — Goal

Add `fuel_type` field to `vehicles` table, backed by `VehicleFuelTypeEnum` (gasoline|diesel). Expose in form, resource, types, i18n.

---

## §C — Constraints

- Enum: `App\Enums\VehicleFuelTypeEnum`, cases `GASOLINE='gasoline'`, `DIESEL='diesel'`
- Migration: 3-step — add nullable → backfill all rows to `diesel` → make NOT NULL
- Pattern mirrors `color` field migration (`2026_04_17_071647_update_vehicles_table.php`)
- Controller validates `fuel_type` as required, must be valid enum value
- `Vehicle` model: add to `#[Fillable]`, add cast, add to `VehicleResource`
- Factory must include `fuel_type`
- i18n keys added to both `it` and `en` locales
- TS `Vehicle` type in `resources/js/types/models.ts` must include `fuel_type`
- Form uses `v-select` (Vuetify 4), consistent with existing form style

---

## §I — Interfaces

| id | surface | path |
|----|---------|------|
| I.enum | VehicleFuelTypeEnum | `app/Enums/VehicleFuelTypeEnum.php` |
| I.model | Vehicle model | `app/Models/Vehicle.php` |
| I.resource | VehicleResource | `app/Http/Resources/VehicleResource.php` |
| I.controller | VehicleController store/update | `app/Http/Controllers/VehicleController.php` |
| I.factory | VehicleFactory | `database/factories/VehicleFactory.php` |
| I.migration | new migration | `database/migrations/*_add_fuel_type_to_vehicles_table.php` |
| I.types | TS Vehicle type | `resources/js/types/models.ts` |
| I.i18n_it | Italian locale | `resources/js/i18n/locales/it.ts` |
| I.i18n_en | English locale | `resources/js/i18n/locales/en.ts` |
| I.form | vehicles Form.vue | `resources/js/pages/vehicles/Form.vue` |

---

## §V — Invariants

| id | invariant |
|----|-----------|
| V1 | `fuel_type` column NOT NULL in DB after migration |
| V2 | Controller rejects any value not in `VehicleFuelTypeEnum` (store + update) |
| V3 | Migration backfills all existing rows to `diesel` before adding NOT NULL constraint |
| V4 | `fuel_type` required in store and update validation |
| V5 | `Vehicle` model casts `fuel_type` to `VehicleFuelTypeEnum` |
| V6 | `VehicleResource` exposes `fuel_type` as string (enum value) |
| V7 | `Form.vue` renders `v-select` with gasoline + diesel options |
| V8 | i18n keys `vehicles.fuel_type`, `vehicles.fuel_types.gasoline`, `vehicles.fuel_types.diesel` exist in both locales |
| V9 | `VehicleFactory` includes `fuel_type` defaulting to a random enum value |

---

## §T — Tasks

| id | status | goal | cites |
|----|--------|------|-------|
| T1 | x | Create `VehicleFuelTypeEnum` | V2,V5,I.enum |
| T2 | x | Migration: add nullable, backfill diesel, make NOT NULL | V1,V3,I.migration |
| T3 | x | Update `Vehicle` model: Fillable, cast; update `VehicleResource` | V5,V6,I.model,I.resource |
| T4 | x | Update `VehicleController` store+update validation | V2,V4,I.controller |
| T5 | x | Update `VehicleFactory` with `fuel_type` | V9,I.factory |
| T6 | x | Add i18n keys to `it` and `en` locales | V8,I.i18n_it,I.i18n_en |
| T7 | x | Add `fuel_type` to TS `Vehicle` type | I.types |
| T8 | x | Update `Form.vue`: add `v-select` for fuel_type | V7,I.form |
| T9 | x | Update `VehicleTest`: add fuel_type to payloads, add validation test | V2,V4 |

---

## §B — Bug Log

| id | date | cause | fix |
|----|------|-------|-----|