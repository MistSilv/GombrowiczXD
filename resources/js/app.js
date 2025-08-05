//import plików js
import './burger.js';
import './niewlasne.js';
import './straty-create.js';
import './wsady-create.js';
import './zamowienia-create.js';

// Importy niezbędnych zależności
import './bootstrap';
import { registerSW } from 'virtual:pwa-register';
import { Html5Qrcode } from 'html5-qrcode';

// Rejestracja Service Workera 
const updateSW = registerSW({
  scope: '/', // 🔧 To zapewnia pełne przechwycenie całego ruchu
  onNeedRefresh() {
    if (confirm('Nowa wersja aplikacji jest dostępna. Odświeżyć?')) {
      updateSW(true);
    }
  },
  onRegistered(reg) {
    console.log('✅ Service Worker registered with scope:', reg?.scope);
  },
  onRegisterError(error) {
    console.error('❌ SW registration failed:', error);
  },
});

// Zmienna pomocnicza do przechwycenia zdarzenia instalacji PWA
let deferredPrompt;

// Przycisk instalacji PWA z interfejsu 
const installBtn = document.getElementById('installPWA');

// Nasłuchiwanie zdarzenia: przeglądarka gotowa zaproponować instalację PWA
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault(); // Blokowanie domyślnego zachowania przeglądarki
    deferredPrompt = e; // Zapamiętanie zdarzenia instalacji
    if (installBtn) installBtn.style.display = 'block'; // Pokazanie przycisku instalacji
});

// Obsługa kliknięcia przycisku instalacji PWA
if (installBtn) {
    installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return; // Jeśli nie ma zdarzenia instalacji, nic nie rób

        deferredPrompt.prompt(); // Wyświetlenie natywnego okna instalacji
        const { outcome } = await deferredPrompt.userChoice; // Oczekiwanie na wybór użytkownika

        if (outcome === 'accepted') {
            installBtn.style.display = 'none'; // Ukrycie przycisku po zaakceptowaniu
        }

        deferredPrompt = null; // Reset zmiennej
    });
}

// Automatyczne sprawdzenie sesji po odzyskaniu połączenia internetowego
window.addEventListener('online', async () => {
    try {
        const response = await fetch('/welcome', { credentials: 'same-origin' });

        // Jeśli serwer przekierował na login lub błąd autoryzacji, przeładuj stronę logowania
        if (response.redirected && response.url.includes('/login')) {
            window.location.href = '/login';
        }
        if (response.status === 401 || response.status === 419) {
            window.location.href = '/login';
        }
    } catch (e) {
    }
});
