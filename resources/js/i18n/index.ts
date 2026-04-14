import { createI18n } from 'vue-i18n';
import { en as vuetifyEn, it as vuetifyIt } from 'vuetify/locale';
import en from './locales/en';
import it from './locales/it';

export const i18n = createI18n({
    legacy: false,
    locale: 'it',
    fallbackLocale: 'en',
    messages: {
        it: { ...it, $vuetify: vuetifyIt },
        en: { ...en, $vuetify: vuetifyEn },
    },
});
