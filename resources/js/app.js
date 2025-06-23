import './bootstrap';

let deferredPrompt;
const installBtn = document.getElementById('installPwaBtn');
if (installBtn) installBtn.style.display = 'none';

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    if (installBtn) installBtn.style.display = 'block';
});

if (installBtn) {
    installBtn.addEventListener('click', () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.finally(() => {
                installBtn.style.display = 'none';
                deferredPrompt = null;
            });
        }
    });
}