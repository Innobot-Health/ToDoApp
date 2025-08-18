import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
    test: {
        globals: true,       // <-- enables describe, it, expect globally
        environment: 'jsdom' // <-- required for Vue component testing
    },
});
