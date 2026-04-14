<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { DEFAULT_PRIMARY, useAppTheme } from '@/composables/useAppTheme';
import { color as colorRoute, locale, theme as themeRoute } from '@/routes/settings';

const props = defineProps<{
    locales: { value: string; label: string }[];
    currentLocale: string;
    primaryColor: string;
    colorScheme: string;
}>();

const { t } = useI18n();
const { applyPrimaryColor, applyColorScheme } = useAppTheme();

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
</script>

<template>
    <Head :title="t('settings.title')" />

    <v-container>
        <h1 class="text-h5 mb-6">{{ t('settings.title') }}</h1>

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
</template>
