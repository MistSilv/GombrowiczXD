import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';
import fs from 'fs';

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
                    {
                        src: '/images/icons/icon-72x72.png',
                        sizes: '72x72',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/icon-96x96.png',
                        sizes: '96x96',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/icon-128x128.png',
                        sizes: '128x128',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/icon-144x144.png',
                        sizes: '144x144',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/icon-384x384.png',
                        sizes: '384x384',
                        type: 'image/png',
                    },
                ],
                screenshots: [
                    {
                        src: '/images/icons/splash-640x1136.png',
                        sizes: '640x1136',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/splash-750x1334.png',
                        sizes: '750x1334',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/splash-828x1792.png',
                        sizes: '828x1792',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/splash-1125x2436.png',
                        sizes: '1125x2436',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/splash-1242x2208.png',
                        sizes: '1242x2208',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/splash-1242x2688.png',
                        sizes: '1242x2688',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/splash-1536x2048.png',
                        sizes: '1536x2048',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/splash-1668x2224.png',
                        sizes: '1668x2224',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/splash-1668x2388.png',
                        sizes: '1668x2388',
                        type: 'image/png',
                    },
                    {
                        src: '/images/icons/splash-2048x2732.png',
                        sizes: '2048x2732',
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
    server: {
        port: 5174,
        https: {
            key: fs.readFileSync('./cert/gombrowiczxd.test-key.pem'),
            cert: fs.readFileSync('./cert/gombrowiczxd.test.pem'),
        },
        strictPort: true,
        hmr: {
            host: 'gombrowiczxd.test',
            protocol: 'wss'
        }
    },
});