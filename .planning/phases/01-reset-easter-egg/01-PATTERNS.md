# Phase 1: Reset Easter Egg - Pattern Map

**Mapped:** 2026-04-16
**Files analyzed:** 5
**Analogs found:** 5 / 5

## File Classification

| New/Modified File | Role | Data Flow | Closest Analog | Match Quality |
|---|---|---|---|---|
| `resources/js/pages/Settings.vue` | component | request-response | itself (existing import dialog) | exact |
| `app/Http/Controllers/SettingsController.php` | controller | request-response | itself (existing `import()` method) | exact |
| `resources/js/i18n/locales/en.ts` | config | transform | itself (existing `settings.*` block) | exact |
| `resources/js/i18n/locales/it.ts` | config | transform | itself (existing `settings.*` block) | exact |
| `routes/web.php` | config | request-response | itself (existing `settings.import` route) | exact |

---

## Pattern Assignments

### `resources/js/pages/Settings.vue` (component, request-response)

**Analog:** Same file — copy patterns from the import dialog section.

**Script imports pattern** (lines 1-7):
```typescript
import { Head, router, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { DEFAULT_PRIMARY, useAppTheme } from '@/composables/useAppTheme';
import { color as colorRoute, exportMethod, importMethod, locale, theme as themeRoute } from '@/routes/settings';
```
Add `resetDb` to the Wayfinder import from `@/routes/settings` after the route is registered and `wayfinder:generate` is run.

**Loading ref pattern** (line 58):
```typescript
const importLoading = ref(false);
```
Mirror exactly for reset: `const resetLoading = ref(false);`

**Modal state ref pattern** (line 59):
```typescript
const showConfirm = ref(false);
```
Mirror exactly: `const showResetConfirm = ref(false);`

**Flash-driven snackbar refs** (lines 63-64):
```typescript
const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');
```
Reuse these same refs — no new refs needed for snackbar state.

**Flash watcher pattern** (lines 69-75):
```typescript
watch(flashSuccess, (val) => {
    if (val) {
        snackbarMessage.value = t(val);
        snackbarColor.value = 'success';
        snackbar.value = true;
    }
});
```
This watcher already handles `page.props.flash?.success`. The backend flash key `settings.reset_success` will be picked up automatically — no change needed here.

**Inertia router.post with loading state pattern** (lines 111-116):
```typescript
router.post(importMethod.url(), formData, {
    forceFormData: true,
    preserveScroll: true,
    onFinish: () => { importLoading.value = false; },
});
```
Mirror for reset (no FormData needed — simple POST with no body):
```typescript
function confirmReset(): void {
    showResetConfirm.value = false;
    resetLoading.value = true;
    router.post(resetDb.url(), {}, {
        preserveScroll: true,
        onFinish: () => { resetLoading.value = false; },
    });
}
```

**Click counter with 2-second window pattern** — no existing analog; implement as:
```typescript
const resetClickCount = ref(0);
let resetClickTimer: ReturnType<typeof setTimeout> | null = null;

function onTitleClick(): void {
    resetClickCount.value++;
    if (resetClickTimer) clearTimeout(resetClickTimer);
    if (resetClickCount.value >= 5) {
        resetClickCount.value = 0;
        showResetConfirm.value = true;
        return;
    }
    resetClickTimer = setTimeout(() => {
        resetClickCount.value = 0;
    }, 2000);
}
```

**h1 click binding pattern** (line 128):
```html
<h1 class="text-h5 mb-6">{{ t('settings.title') }}</h1>
```
Add `@click="onTitleClick"` — no visual change, no cursor change (hidden easter egg per D-03).

**Dialog pattern** (lines 225-234):
```html
<v-dialog v-model="showConfirm" max-width="360">
    <v-card rounded="xl">
        <v-card-text class="pt-6">{{ t('settings.import_confirm') }}</v-card-text>
        <v-card-actions>
            <v-spacer />
            <v-btn variant="text" @click="cancelImport">{{ t('common.cancel') }}</v-btn>
            <v-btn color="error" variant="tonal" @click="confirmImport">{{ t('settings.import_db') }}</v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>
```
Copy this structure exactly for reset dialog, substituting `showResetConfirm`, `cancelReset`, `confirmReset`, and the reset i18n keys. Add `:loading="resetLoading"` to the confirm button (per D-09).

**Snackbar pattern** (lines 237-239):
```html
<v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000" location="bottom">
    {{ snackbarMessage }}
</v-snackbar>
```
Already present — no change needed.

---

### `app/Http/Controllers/SettingsController.php` (controller, request-response)

**Analog:** Same file — `import()` method is the direct pattern to copy from.

**Namespace and imports pattern** (lines 1-14):
```php
namespace App\Http\Controllers;

use App\Enums\Locale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;
use Native\Mobile\Facades\Share;
```
Add `use Illuminate\Support\Facades\Artisan;` for `Artisan::call('db:seed')`.

**Truncation pattern** (lines 104-112):
```php
DB::statement('PRAGMA foreign_keys = OFF');

DB::transaction(function () use ($data) {
    DB::table('vehicle_service_reminders')->delete();
    DB::table('vehicle_services')->delete();
    DB::table('vehicle_refuels')->delete();
    DB::table('vehicles')->delete();
    DB::table('vehicle_service_types')->delete();
    // ... inserts follow
});

DB::statement('PRAGMA foreign_keys = ON');
```
The `resetDb()` method truncates in the same dependency order without any inserts, then calls `Artisan::call('db:seed')`:
```php
public function resetDb(): RedirectResponse
{
    DB::statement('PRAGMA foreign_keys = OFF');

    DB::transaction(function () {
        DB::table('vehicle_service_reminders')->delete();
        DB::table('vehicle_services')->delete();
        DB::table('vehicle_refuels')->delete();
        DB::table('vehicles')->delete();
        DB::table('vehicle_service_types')->delete();
    });

    DB::statement('PRAGMA foreign_keys = ON');

    Artisan::call('db:seed');

    return redirect()->back()->with('success', 'settings.reset_success');
}
```

**Flash success return pattern** (line 132):
```php
return redirect()->back()->with('success', 'settings.import_success');
```
Mirror with `'settings.reset_success'` — frontend flash watcher picks it up automatically.

---

### `resources/js/i18n/locales/en.ts` (config, transform)

**Analog:** Same file — add 4 keys to the `settings` block at lines 120-143.

**Existing `settings` block tail** (lines 138-143):
```typescript
    import_confirm: 'All existing data will be replaced. Continue?',
    import_success: 'Database imported successfully.',
    import_error: 'Invalid backup file.',
    export_error: 'Export failed.',
},
```
Append after `export_error` (before closing `},`):
```typescript
    reset_db: 'Factory reset',
    reset_db_desc: 'Wipe all data and restore defaults.',
    reset_confirm: 'This will permanently delete ALL data and restore the default seed. This action cannot be undone.',
    reset_success: 'Database reset successfully.',
```

---

### `resources/js/i18n/locales/it.ts` (config, transform)

**Analog:** Same file — add 4 keys to the `settings` block at lines 121-144.

**Existing `settings` block tail** (lines 141-144):
```typescript
    import_confirm: 'Tutti i dati esistenti verranno sostituiti. Continuare?',
    import_success: 'Database importato con successo.',
    import_error: 'File di backup non valido.',
    export_error: 'Esportazione fallita.',
},
```
Append after `export_error` (before closing `},`):
```typescript
    reset_db: 'Reset di fabbrica',
    reset_db_desc: 'Cancella tutti i dati e ripristina i valori predefiniti.',
    reset_confirm: 'Questa operazione eliminerà TUTTI i dati e ripristinerà i dati predefiniti. L\'operazione non può essere annullata.',
    reset_success: 'Database reimpostato con successo.',
```

---

### `routes/web.php` (config, request-response)

**Analog:** Same file — line 30 is the direct pattern.

**Existing settings POST route pattern** (line 30):
```php
Route::post('/settings/import', [SettingsController::class, 'import'])->name('settings.import');
```
Add immediately after line 30:
```php
Route::post('/settings/reset', [SettingsController::class, 'resetDb'])->name('settings.reset');
```
Then run `php artisan wayfinder:generate` to regenerate `@/routes/settings` so `resetDb` is available as a typed import in Settings.vue.

---

## Shared Patterns

### Flash-driven snackbar (no change needed)
**Source:** `resources/js/pages/Settings.vue` lines 66-83
**Apply to:** `Settings.vue` reset flow

The existing `flashSuccess` computed + watcher already translates any `page.props.flash.success` key via `t(val)`. The backend returning `->with('success', 'settings.reset_success')` and the new i18n key being present in both locale files is all that is required — no frontend watcher changes.

### PRAGMA foreign_keys guard
**Source:** `app/Http/Controllers/SettingsController.php` lines 104 and 130
**Apply to:** `resetDb()` method

Always wrap truncation with `DB::statement('PRAGMA foreign_keys = OFF')` before and `DB::statement('PRAGMA foreign_keys = ON')` after the transaction — required for SQLite cascade safety.

### Destructive action confirmation dialog structure
**Source:** `resources/js/pages/Settings.vue` lines 225-234
**Apply to:** Reset modal in Settings.vue

Structure: `v-dialog` → `v-card rounded="xl"` → `v-card-text.pt-6` for message → `v-card-actions` with `v-spacer`, cancel `variant="text"`, confirm `color="error" variant="tonal"`.

---

## No Analog Found

None — all 5 files have direct analogs in the existing codebase.

---

## Metadata

**Analog search scope:** `resources/js/pages/`, `app/Http/Controllers/`, `resources/js/i18n/locales/`, `routes/`
**Files scanned:** 5
**Pattern extraction date:** 2026-04-16
