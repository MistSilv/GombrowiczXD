import './bootstrap';

let deferredPrompt;
const installBtn = document.getElementById('installPwaBtn');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    if (installBtn) {
        installBtn.style.display = 'block';
        installBtn.addEventListener('click', () => {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(() => {
                installBtn.style.display = 'none';
                deferredPrompt = null;
            });
        });
    }
});