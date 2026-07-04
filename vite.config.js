import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Testbench serves from its own laravel/public docroot, so the hot file and
// build manifest must land there for @vite to resolve them at runtime.
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'workbench/resources/css/app.css',
                'workbench/resources/js/app.js',
            ],
            publicDirectory: 'vendor/orchestra/testbench-core/laravel/public',
            refresh: [
                'workbench/resources/views/**',
                'workbench/routes/**',
                'src/**',
            ],
        }),
        tailwindcss(),
    ],
});
