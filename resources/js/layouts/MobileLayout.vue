<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useAppTheme } from '@/composables/useAppTheme';
import { i18n } from '@/i18n';

const { applyPrimaryColor, applyColorScheme } = useAppTheme();
const page = usePage();

watch(
    () => page.props.locale as string,
    (newLocale: string) => {
        i18n.global.locale.value = newLocale as 'it' | 'en';
    },
    { immediate: true },
);

watch(
    () => page.props.primaryColor as string,
    (newColor: string) => {
        applyPrimaryColor(newColor);
    },
    { immediate: true },
);

watch(
    () => page.props.colorScheme as string,
    (newScheme: string) => {
        applyColorScheme(newScheme);
    },
    { immediate: true },
);
</script>

<template>
    <v-app>
        <v-main :style="{ paddingTop: 'env(safe-area-inset-top, 0px)' }">
            <slot />
        </v-main>
    </v-app>
</template>
