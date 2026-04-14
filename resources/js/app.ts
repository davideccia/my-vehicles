import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { useI18n } from 'vue-i18n';
import { createVuetify } from 'vuetify';
import { createVueI18nAdapter } from 'vuetify/locale/adapters/vue-i18n';
import { i18n } from '@/i18n';
import MobileLayout from '@/layouts/MobileLayout.vue';

const vuetify = createVuetify({
    locale: {
        adapter: createVueI18nAdapter({ i18n, useI18n }),
    },
    theme: {
        defaultTheme: 'dark',
        themes: {
            dark: {
                colors: {
                    primary: '#6750A4',
                    secondary: '#AA80FF',
                },
            },
            light: {
                colors: {
                    primary: '#6750A4',
                    secondary: '#AA80FF',
                },
            },
        },
    },
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: () => MobileLayout,
    setup({ el, App, props, plugin }) {
        const locale = (props.initialPage.props as Record<string, unknown>).locale as string | undefined;

        if (locale) {
            i18n.global.locale.value = locale as 'it' | 'en';
        }

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .use(vuetify)
            .mount(el);
    },
    progress: {
        color: '#1867C0',
    },
});
