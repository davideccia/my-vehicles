<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { DEFAULT_PRIMARY, useAppTheme } from '@/composables/useAppTheme';
import { color as colorRoute, locale, reset as resetDb, theme as themeRoute } from '@/routes/settings';

const props = defineProps<{
    locales: { value: string; label: string }[];
    currentLocale: string;
    primaryColor: string;
    colorScheme: string;
}>();

const { t } = useI18n();
const { applyPrimaryColor, applyColorScheme } = useAppTheme();
const page = usePage<{ flash?: { success?: string }; errors?: { file?: string } }>();

// ── Appearance ────────────────────────────────────────────────────────────────

const selectedTheme = ref<string>(props.colorScheme || 'dark');

function onThemeChange(value: string): void {
    applyColorScheme(value);
    router.post(themeRoute.url(), { theme: value }, { preserveScroll: true });
}

const selectedColor = ref<string>(props.primaryColor || DEFAULT_PRIMARY);

const colorSwatches = [
    ['#6750A4'],
    ['#0061A4'],
    ['#006E1C'],
    ['#BA1A1A'],
    ['#7B5800'],
    ['#006A6A'],
];

const debouncedSave = useDebounceFn((hex: string) => {
    router.post(colorRoute.url(), { color: hex }, { preserveScroll: true });
}, 500);

watch(selectedColor, (newColor: string) => {
    if (newColor && /^#[0-9A-Fa-f]{6}$/.test(newColor)) {
        applyPrimaryColor(newColor);
        debouncedSave(newColor);
    }
});

function onLocaleChange(value: string): void {
    router.post(locale.url(), { locale: value });
}

// ── Data import / export ──────────────────────────────────────────────────────

const fileInput = ref<HTMLInputElement | null>(null);
const importLoading = ref(false);
const showConfirm = ref(false);
const pendingFile = ref<File | null>(null);

const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

const flashSuccess = computed(() => page.props.flash?.success);
const flashError = computed(() => page.props.errors?.file);

watch(flashSuccess, (val) => {
    if (val) {
        snackbarMessage.value = t(val);
        snackbarColor.value = 'success';
        snackbar.value = true;
    }
});

watch(flashError, (val) => {
    if (val) {
        snackbarMessage.value = t(val);
        snackbarColor.value = 'error';
        snackbar.value = true;
    }
});

function onExport(): void {
    window.location.href = exportMethod.url();
}

function onImportClick(): void {
    fileInput.value?.click();
}

function onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
return;
}

    pendingFile.value = file;
    showConfirm.value = true;
    input.value = '';
}

function confirmImport(): void {
    if (!pendingFile.value) {
return;
}

    showConfirm.value = false;
    importLoading.value = true;

    const formData = new FormData();
    formData.append('file', pendingFile.value);
    pendingFile.value = null;

    router.post(importMethod.url(), formData, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
 importLoading.value = false;
},
    });
}

function cancelImport(): void {
    pendingFile.value = null;
    showConfirm.value = false;
}

// ── Factory reset easter egg ──────────────────────────────────────────────────

const resetLoading = ref(false);
const showResetConfirm = ref(false);
const resetClickCount = ref(0);
let resetClickTimer: ReturnType<typeof setTimeout> | null = null;

function onTitleClick(): void {
    resetClickCount.value++;

    if (resetClickTimer) {
clearTimeout(resetClickTimer);
}

    if (resetClickCount.value >= 5) {
        resetClickCount.value = 0;
        resetClickTimer = null;
        showResetConfirm.value = true;

        return;
    }

    resetClickTimer = setTimeout(() => {
        resetClickCount.value = 0;
    }, 2000);
}

function cancelReset(): void {
    showResetConfirm.value = false;
}

function confirmReset(): void {
    showResetConfirm.value = false;
    resetLoading.value = true;
    router.post(resetDb.url(), {}, {
        preserveScroll: true,
        onFinish: () => {
            resetLoading.value = false;
        },
    });
}
</script>

<template>
    <Head :title="t('settings.title')" />

    <v-container>
        <h1 class="text-h5 mb-6" @click="onTitleClick">{{ t('settings.title') }}</h1>

        <v-divider class="my-4" />

        <h2 class="text-h5 mb-6">{{ t('settings.sections.general') }}</h2>

        <p class="text-body-2 mb-2 text-medium-emphasis">{{ t('settings.language') }}</p>
        <v-btn-toggle
            :model-value="currentLocale"
            mandatory
            rounded="xl"
            class="mb-6"
            @update:model-value="onLocaleChange"
        >
            <v-btn
                v-for="item in locales"
                :key="item.value"
                :value="item.value"
            >
                {{ item.label }}
            </v-btn>
        </v-btn-toggle>

        <h3 class="text-h5 mb-6">{{ t('settings.sections.appearance') }}</h3>

        <p class="text-body-2 mb-2 text-medium-emphasis">{{ t('settings.theme') }}</p>
        <v-btn-toggle
            v-model="selectedTheme"
            mandatory
            rounded="xl"
            class="mb-6"
            @update:model-value="onThemeChange"
        >
            <v-btn value="light" prepend-icon="mdi-weather-sunny">
                {{ t('settings.theme_light') }}
            </v-btn>
            <v-btn value="dark" prepend-icon="mdi-weather-night">
                {{ t('settings.theme_dark') }}
            </v-btn>
        </v-btn-toggle>

        <p class="text-body-2 mb-4 text-medium-emphasis">{{ t('settings.primary_color') }}</p>
        <v-color-picker
            v-model="selectedColor"
            rounded="xl"
            :modes="['hex']"
            mode="hex"
            :swatches="colorSwatches"
            show-swatches
            hide-inputs
            width="100%"
        />

    </v-container>

    <!-- Confirm dialog -->
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

    <!-- Reset confirm dialog -->
    <v-dialog v-model="showResetConfirm" max-width="360">
        <v-card rounded="xl">
            <v-card-text class="pt-6">{{ t('settings.reset_confirm') }}</v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn variant="text" @click="cancelReset">{{ t('common.cancel') }}</v-btn>
                <v-btn color="error" variant="tonal" :loading="resetLoading" @click="confirmReset">{{ t('settings.reset_db') }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <!-- Snackbar feedback -->
    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000" location="bottom">
        {{ snackbarMessage }}
    </v-snackbar>
</template>
