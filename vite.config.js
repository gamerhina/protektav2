import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/signature-pad.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],

    // ⬇️ INI KUNCI UTAMA
    build: {
        outDir: 'build', 
        emptyOutDir: true,
    },

    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
});
