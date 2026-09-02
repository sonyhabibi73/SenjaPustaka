import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/main.js',
                'resources/js/reader.js',
            ],
            refresh: true,
        }),
    ],
});
