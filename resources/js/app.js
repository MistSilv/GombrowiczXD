import './bootstrap';
import { registerSW } from 'virtual:pwa-register';

navigator.serviceWorker.register('build/sw.js');
//registerSW();

let deferredPrompt;
const installBtn = document.getElementById('installPWA');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    if (installBtn) installBtn.style.display = 'block';
});

if (installBtn) {
    installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') {
            installBtn.style.display = 'none';
        }
        deferredPrompt = null;
    });
}


window.addEventListener('online', async () => {
    try {
        const response = await fetch('/welcome', { credentials: 'same-origin' });
        if (response.redirected && response.url.includes('/login')) {
            window.location.href = '/login';
        }
        if (response.status === 401 || response.status === 419) {
            window.location.href = '/login';
        }
    } catch (e) {
    }
});

import { Html5Qrcode } from "html5-qrcode";
