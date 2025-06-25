import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            manifest: {
                name: 'Gombrowicz',
                short_name: 'Gombrowicz',
                start_url: '/',
                display: 'standalone',
                background_color: '#000000',
                theme_color: '#1e293b',
                icons: [
                    {
                        src: '/images/icons/icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                ],
            },
            devOptions: {
                enabled: true
            },
            workbox: {
        navigateFallback: '/offline.html', 
        runtimeCaching: [
            {
                // Cache strony HTML (np. podstrony Laravel)
                urlPattern: /^\/.*$/,
                handler: 'NetworkFirst',
                options: {
                    cacheName: 'html-pages',
                    expiration: {
                        maxEntries: 50,
                        maxAgeSeconds: 24 * 60 * 60, 
                    },
                },
            },
            {
                // Cache CSS, JS, obrazków
                urlPattern: /\.(?:js|css|png|jpg|jpeg|svg|gif)$/,
                handler: 'CacheFirst',
                options: {
                    cacheName: 'assets',
                    expiration: {
                        maxEntries: 100,
                        maxAgeSeconds: 7 * 24 * 60 * 60,
                    },
                },
            },
            ],
        },
        }),
    ],
});