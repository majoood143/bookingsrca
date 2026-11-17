import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js',
                'resources/css/games.css',
                'resources/js/games/penalty.js',
                'resources/js/games/leaderboard.js',
                'resources/js/ar/scanner.js',
                'resources/js/ar/compiler.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
