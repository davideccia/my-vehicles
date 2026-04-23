# SPEC.md

## §G — Goal

Replace Vuetify `v-bottom-navigation` in `MobileLayout.vue` with NativePHP EDGE `native:bottom-nav` in `app.blade.php`. 5 nav items. Active state via PHP `request()->is()`. Labels via Laravel `__()`.

---

## §C — Constraints

- `native:bottom-nav` goes in `app.blade.php` (Blade, not Vue)
- Items: Vehicles `/vehicles`, Refuels `/vehicle-refuels`, Services `/vehicle-services`, Reports `/reports`, Settings `/settings`
- Active state per item: `:active="request()->is('vehicles*')"` pattern
- Labels from Laravel lang files (`lang/en/app.php`, `lang/it/app.php`) via `__('app.nav.*')`
- `HandleLocale` middleware already sets `App::setLocale()` — `__()` respects locale
- Icons: use NativePHP system icon names (platform-specific fallback allowed)
- `v-bottom-navigation`, keyboard detection, `iconSize`/`navHeight` computed, `useDisplay` import all removed from `MobileLayout.vue`
- `v-main` padding-bottom adjusted: remove hardcoded `88px` nav offset (native nav owns its space)
- `label-visibility="labeled"` on container

---

## §I — Interfaces

| id | surface | path |
|----|---------|------|
| I.blade | Blade layout | `resources/views/app.blade.php` |
| I.layout | Vue layout | `resources/js/layouts/MobileLayout.vue` |
| I.lang-en | English translations | `lang/en/app.php` |
| I.lang-it | Italian translations | `lang/it/app.php` |

---

## §V — Invariants

| id | invariant |
|----|-----------|
| V1 | `native:bottom-nav` in `app.blade.php` with exactly 5 items |
| V2 | Each item has unique `id`, correct `url` (route path), `icon`, `label` via `__()`, `:active` via `request()->is()` |
| V3 | `v-bottom-navigation` block removed from `MobileLayout.vue` |
| V4 | Keyboard detection (`isKeyboardOpen`, `handleFocusIn`, `handleFocusOut`, `onMounted`/`onUnmounted` listeners) removed from `MobileLayout.vue` |
| V5 | `iconSize`, `navHeight` computed props removed from `MobileLayout.vue` |
| V6 | `useDisplay` import removed from `MobileLayout.vue` |
| V7 | `v-main` padding-bottom no longer reserves `88px` for Vuetify nav |
| V8 | `lang/en/app.php` and `lang/it/app.php` contain `nav.vehicles`, `nav.refuels`, `nav.services`, `nav.reports`, `nav.settings` keys |
| V9 | `<style scoped>` block with `.nav-btn` and `.nav-label` removed from `MobileLayout.vue` |

---

## §T — Tasks

| id | status | goal | cites |
|----|--------|------|-------|
| T1 | x | Add nav translation keys to `lang/en/app.php` and `lang/it/app.php` | V8,I.lang-en,I.lang-it |
| T2 | x | Add `native:bottom-nav` to `app.blade.php` | V1,V2,I.blade |
| T3 | . | Remove `v-bottom-navigation` and cleanup from `MobileLayout.vue` | V3,V4,V5,V6,V7,V9,I.layout |

---

## §B — Bug Log

| id | date | cause | fix |
|----|------|-------|-----|