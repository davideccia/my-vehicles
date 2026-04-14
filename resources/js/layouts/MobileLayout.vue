<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useDisplay } from 'vuetify';
import { useI18n } from 'vue-i18n';
import { useAppTheme } from '@/composables/useAppTheme';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { i18n } from '@/i18n';
import { show as reports } from '@/routes/reports';
import { show as settings } from '@/routes/settings';
import { index as refuelsIndex } from '@/routes/vehicle-refuels';
import { index as servicesIndex } from '@/routes/vehicle-services';
import { index } from '@/routes/vehicles';

const { t } = useI18n();
const { width } = useDisplay();
const { isCurrentOrParentUrl } = useCurrentUrl();
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

const navItems = [
    { labelKey: 'nav.vehicles', href: index.url(), icon: 'mdi-car' },
    { labelKey: 'nav.refuels', href: refuelsIndex.url(), icon: 'mdi-gas-station' },
    { labelKey: 'nav.services', href: servicesIndex.url(), icon: 'mdi-wrench' },
    { labelKey: 'nav.reports', href: reports.url(), icon: 'mdi-chart-line' },
    { labelKey: 'nav.settings', href: settings.url(), icon: 'mdi-cog' },
];

const activeNav = computed(() => {
    const active = navItems.find((item) => isCurrentOrParentUrl(item.href));

    return active ? active.href : null;
});

const iconSize = computed(() => {
    if (width.value < 340) return '16';
    if (width.value < 380) return '18';
    return '20';
});

const navHeight = computed(() => {
    if (width.value < 340) return 56;
    if (width.value < 380) return 60;
    return 64;
});

const isKeyboardOpen = ref(false);

function handleFocusIn(e: FocusEvent) {
    const target = e.target as HTMLElement;

    if (target.matches('input, textarea, select, [contenteditable]')) {
        isKeyboardOpen.value = true;
    }
}

function handleFocusOut() {
    isKeyboardOpen.value = false;
}

onMounted(() => {
    document.addEventListener('focusin', handleFocusIn);
    document.addEventListener('focusout', handleFocusOut);
});

onUnmounted(() => {
    document.removeEventListener('focusin', handleFocusIn);
    document.removeEventListener('focusout', handleFocusOut);
});
</script>

<template>
    <v-app>
        <v-main :style="{ paddingTop: 'env(safe-area-inset-top, 0px)', paddingBottom: 'calc(88px + env(safe-area-inset-bottom, 0px))' }">
            <slot />
        </v-main>

        <v-bottom-navigation
            v-show="!isKeyboardOpen"
            :model-value="activeNav"
            :height="navHeight"
            :style="{ bottom: 'calc(16px + env(safe-area-inset-bottom, 0px))', width: 'calc(100% - 32px)' }"
            class="mx-4"
            rounded="lg"
            elevation="8"
            grow
            bg-color="primary"
            mode="shift"
            density="comfortable"
        >
            <v-btn
                v-for="item in navItems"
                :key="item.labelKey"
                :value="item.href"
                :active="isCurrentOrParentUrl(item.href)"
                class="nav-btn"
                @click="router.visit(item.href)"
            >
                <v-icon :size="iconSize">{{ item.icon }}</v-icon>
                <span class="nav-label">{{ t(item.labelKey) }}</span>
            </v-btn>
        </v-bottom-navigation>
    </v-app>
</template>

<style scoped>
.nav-btn {
    min-width: 0 !important;
    flex: 1 1 0 !important;
    max-width: 20% !important;
    overflow: hidden;
}

.nav-label {
    font-size: clamp(9px, 2.5vw, 12px);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}
</style>
