import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'node:path';
import svgr from "vite-plugin-svgr";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input:  'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
        svgr(),
        tailwindcss(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        chunkSizeWarningLimit: 10000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes("/Accordion/")) {
                        return "accordion";
                    }
                },
            },
        },
    },
});
