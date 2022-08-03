import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import livewire, { defaultWatches } from '@defstudio/vite-livewire-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),

        livewire({
            refresh: ['resources/css/app.css'],
            watch: [
                ...defaultWatches,
                '**/domains/**/Livewire/**/*.php',
            ]
        }),
    ],
});