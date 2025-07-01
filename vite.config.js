import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';
import fs from 'fs';

// Główna konfiguracja Vite
export default defineConfig({

    plugins: [

        // Integracja z Laravel - automatyczny reload i podpięcie plików startowych
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'], // Pliki wejściowe CSS i JS
            refresh: true, // Automatyczne odświeżanie przy zmianach plików backendowych
        }),

        // Integracja Tailwind CSS jako plugin Vite
        tailwindcss(),

        // Konfiguracja PWA
        VitePWA({
            filename: 'sw.js', // Nazwa pliku Service Workera
            registerType: 'autoUpdate', // Automatyczna aktualizacja Service Workera

            includeAssets: [
                'favicon.ico',
                'robots.txt',
                'offline.html', // <-- dodaj to!
                // możesz dodać inne pliki statyczne, jeśli chcesz
            ],

            // Plik manifestu aplikacji PWA
            manifest: {
                name: 'Gombrowicz',          
                short_name: 'Gombrowicz',    
                start_url: '/',      
                scope: '/',        
                display: 'standalone',       
                background_color: '#000000', 
                theme_color: '#1e293b',

                // Ikony aplikacji w różnych rozdzielczościach
                icons: [
                    { src: '/images/icons/icon-192x192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/images/icons/icon-512x512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/images/icons/icon-72x72.png', sizes: '72x72', type: 'image/png' },
                    { src: '/images/icons/icon-96x96.png', sizes: '96x96', type: 'image/png' },
                    { src: '/images/icons/icon-128x128.png', sizes: '128x128', type: 'image/png' },
                    { src: '/images/icons/icon-144x144.png', sizes: '144x144', type: 'image/png' },
                    { src: '/images/icons/icon-384x384.png', sizes: '384x384', type: 'image/png' },
                ],

                // Ekrany powitalne (splash screen) dla różnych rozdzielczości
                screenshots: [
                    { src: '/images/icons/splash-640x1136.png', sizes: '640x1136', type: 'image/png' },
                    { src: '/images/icons/splash-750x1334.png', sizes: '750x1334', type: 'image/png' },
                    { src: '/images/icons/splash-828x1792.png', sizes: '828x1792', type: 'image/png' },
                    { src: '/images/icons/splash-1125x2436.png', sizes: '1125x2436', type: 'image/png' },
                    { src: '/images/icons/splash-1242x2208.png', sizes: '1242x2208', type: 'image/png' },
                    { src: '/images/icons/splash-1242x2688.png', sizes: '1242x2688', type: 'image/png' },
                    { src: '/images/icons/splash-1536x2048.png', sizes: '1536x2048', type: 'image/png' },
                    { src: '/images/icons/splash-1668x2224.png', sizes: '1668x2224', type: 'image/png' },
                    { src: '/images/icons/splash-1668x2388.png', sizes: '1668x2388', type: 'image/png' },
                    { src: '/images/icons/splash-2048x2732.png', sizes: '2048x2732', type: 'image/png', form_factor: 'wide' },
                ],
            },
            workbox: {
                navigateFallback: '/offline.html',
                additionalManifestEntries: [
                    { url: '/offline.html', revision: null },
                ],
            },
            // Włączenie PWA również w środowisku developerskim
            devOptions: {
                enabled: true
            },
        }),
    ],

    // Konfiguracja serwera developerskiego Vite
    server: {
        port: 5174,

        // HTTPS z lokalnym certyfikatem 
        https: {
            key: fs.readFileSync('./cert/gombrowiczxd.test-key.pem'),
            cert: fs.readFileSync('./cert/gombrowiczxd.test.pem'),
        },

        strictPort: true, 

        // Konfiguracja Hot Module Replacement z obsługą wss (szyfrowane WebSockety)
        hmr: {
            host: 'gombrowiczxd.test', 
            protocol: 'wss'            
        }
    },
});
