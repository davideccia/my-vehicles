import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import vuetify from 'vite-plugin-vuetify';
import { nativephpHotFile, nativephpMobile } from './vendor/nativephp/mobile/resources/js/vite-plugin.js';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            refresh: true,
            hotFile: nativephpHotFile(),
        }),
        inertia({ ssr: false }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({ autoImport: true }),
        wayfinder({
            formVariants: true,
        }),
        nativephpMobile(),
    ],
});
