import { Html5Qrcode } from "html5-qrcode";

window.isFirmoweWifi = 0;

function checkNetworkType() {
    fetch('/api/network/check')
        .then(res => res.json())
        .then(data => {
            window.isFirmoweWifi = data.connection_type === 'wifi_firmowe' ? 1 : 0;
            console.log(window.isFirmoweWifi ? 'Połączono z siecią firmową WiFi' : 'Nie jesteś połączony z siecią firmową WiFi');
        })
        .catch(() => {
            window.isFirmoweWifi = 0;
        });
}

document.addEventListener('DOMContentLoaded', () => {
    checkNetworkType();
    aktualizujDostepneProdukty();
});

window._produkty = JSON.parse(document.getElementById('produkty-data').textContent);

function aktualizujDostepneProdukty() {
    const uzyteProdukty = new Set();
    document.querySelectorAll('#produkty-lista tbody tr').forEach(tr => {
        const pid = parseInt(tr.getAttribute('data-produkt-id'));
        if (pid) uzyteProdukty.add(pid);
    });

    // Tu masz kod na selecty — jeśli nie ma selectów, pomińmy (u Ciebie ich nie ma)
    // Przycisk dodawania produktu:
    const btnDodaj = document.getElementById('dodaj-produkt');
    if (btnDodaj) {
        btnDodaj.disabled = uzyteProdukty.size >= window._produkty.length;
        btnDodaj.classList.toggle('opacity-50', btnDodaj.disabled);
        btnDodaj.classList.toggle('cursor-not-allowed', btnDodaj.disabled);
    }
    return uzyteProdukty;
}

function dodajWiersz(produktId = '', ilosc = '') {
    const tbody = document.querySelector('#produkty-lista tbody');
    const uzyteProdukty = aktualizujDostepneProdukty();

    if (produktId && uzyteProdukty.has(produktId)) {
        setQuantity(produktId, ilosc);
        return;
    }

    const produkt = window._produkty.find(p => p.id === produktId);
    const nazwa = produkt ? produkt.tw_nazwa : '';

    const tr = document.createElement('tr');
    tr.setAttribute('data-produkt-abaco', produkt?.tw_idabaco || '');
    tr.setAttribute('data-produkt-id', produktId || '');
    tr.innerHTML = `
        <td class="border border-gray-300 px-3 py-2 text-left">${nazwa}</td>
        <td class="border border-gray-300 px-3 py-2 text-right">
            <input
                type="number"
                name="ilosci[${produktId}]"
                min="0" max="3000" step="1"
                value="${ilosc || 0}"
                class="border rounded px-3 py-1 w-24 text-right text-black focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
            />
        </td>
        <td class="border border-gray-300 px-3 py-2 text-center">
            <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded transition remove-row">✕</button>
        </td>
    `;

    tbody.appendChild(tr);

    const inputIlosc = tr.querySelector('input[type="number"]');
    if (inputIlosc) {
        inputIlosc.focus();
        inputIlosc.select();
    }

    aktualizujDostepneProdukty();
}

function setQuantity(produktId, ilosc) {
    const rows = document.querySelectorAll('#produkty-lista tbody tr');
    let found = false;

    for (const row of rows) {
        if (parseInt(row.getAttribute('data-produkt-id')) === produktId) {
            const input = row.querySelector('input[type="number"]');
            if (input) {
                const currentValue = parseInt(input.value) || 0;
                input.value = currentValue + ilosc;

                input.focus();
                input.select();
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                found = true;
            }
            break;
        }
    }

    if (!found) {
        dodajWiersz(produktId, ilosc);
    }
    aktualizujDostepneProdukty();
}

// Usuwanie wiersza
document.addEventListener('click', e => {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        aktualizujDostepneProdukty();
    }
});

// Kliknięcie na nazwę produktu w liście — dodaje 1 sztukę
document.addEventListener('click', e => {
    if (e.target.classList.contains('product-name')) {
        const nazwa = e.target.textContent.trim();
        const produkt = window._produkty.find(p => p.tw_nazwa === nazwa);
        if (produkt) {
            setQuantity(produkt.id, 1);
        }
    }
});

// --- Wyszukiwarka produktów ---
const searchInput = document.getElementById('product-search');
const suggestionsList = document.getElementById('product-suggestions');

function clearSuggestions() {
    suggestionsList.innerHTML = '';
    suggestionsList.classList.add('hidden');
}

function showSuggestions(matches) {
    suggestionsList.innerHTML = '';
    if (matches.length === 0) {
        clearSuggestions();
        return;
    }
    matches.forEach(p => {
        const li = document.createElement('li');
        li.textContent = p.tw_nazwa;
        li.className = 'px-3 py-2 hover:bg-blue-100 cursor-pointer';
        li.dataset.produktId = p.id;
        suggestionsList.appendChild(li);
    });
    suggestionsList.classList.remove('hidden');
}

searchInput.addEventListener('input', () => {
    const val = searchInput.value.trim().toLowerCase();
    if (!val) {
        clearSuggestions();
        return;
    }
    const uzyteProdukty = new Set();
    document.querySelectorAll('#produkty-lista tbody tr').forEach(row => {
        const pid = parseInt(row.getAttribute('data-produkt-id'));
        if (pid) uzyteProdukty.add(pid);
    });

    const matches = window._produkty.filter(p =>
        p.tw_nazwa.toLowerCase().includes(val) &&
        !uzyteProdukty.has(p.id)
    ).slice(0, 10);

    showSuggestions(matches);
});

suggestionsList.addEventListener('click', e => {
    if (e.target.tagName === 'LI') {
        const produktId = parseInt(e.target.dataset.produktId);
        setQuantity(produktId, 0);
        clearSuggestions();
        searchInput.value = '';
    }
});

document.addEventListener('click', e => {
    if (e.target !== searchInput && e.target.parentNode !== suggestionsList) {
        clearSuggestions();
    }
});

// Livewire integration
document.addEventListener('livewire:load', () => {
    Livewire.on('produktClicked', produktId => {
        const produkt = window._produkty.find(p => p.id === produktId);
        if (produkt) {
            setQuantity(produkt.id, 0);
        }
    });
});

// --- Obsługa EAN ---

function handleScannedEan(decodedText) {
    const endpoint = window.isFirmoweWifi === 1 ? '/api/check-ean-firmowe' : '/api/check-ean';

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ kod_ean: decodedText })
    })
    .then(response => {
        if (!response.ok) throw new Error('Nie znaleziono produktu');
        return response.json();
    })
    .then(data => {
        if (!data.success) throw new Error(data.message || 'Nie znaleziono produktu');

        const produkt = data.produkt;
        const qty = prompt(`Podaj ilość dla produktu: ${produkt.tw_nazwa}`, "1");

        if (!qty || isNaN(qty) || parseInt(qty) <= 0) throw new Error('Nieprawidłowa ilość');

        let lokalnyProdukt = window._produkty.find(p => p.tw_idabaco === produkt.tw_idabaco);

        if (!lokalnyProdukt) {
            lokalnyProdukt = {
                id: Math.max(0, ...window._produkty.map(p => p.id)) + 1,
                tw_nazwa: produkt.tw_nazwa,
                tw_idabaco: produkt.tw_idabaco,
                ean_codes: produkt.ean_codes || []
            };
            window._produkty.push(lokalnyProdukt);
        }

        setQuantity(lokalnyProdukt.id, parseInt(qty));
    })
    .catch(error => {
        console.error('Błąd:', error);
        alert(error.message);
    });
}

document.getElementById('dodaj-ean').addEventListener('click', () => {
    const eanInput = document.getElementById('product-search-ean');
    const decodedText = eanInput.value.trim();

    if (!decodedText) {
        alert('Wpisz kod EAN/PLU');
        return;
    }

    handleScannedEan(decodedText);
    eanInput.value = '';
});

// --- EAN Scanner ---

const scanner = new Html5Qrcode("reader");
let isScanning = false;

function onScanSuccess(decodedText) {
    console.log("Zeskanowano:", decodedText);

    scanner.stop().then(() => {
        isScanning = false;
        $('#reader').hide();
        $('#scan-result').text(`Zeskanowano: ${decodedText}`);

        handleScannedEan(decodedText);
    }).catch(err => {
        console.error('Błąd zatrzymania skanera:', err);
    });
}

$('#start-scan').on('click', () => {
    if (isScanning) return;

    Html5Qrcode.getCameras()
        .then(devices => {
            if (devices.length) {
                $('#reader').show();
                scanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: 250 },
                    onScanSuccess
                ).then(() => {
                    isScanning = true;
                }).catch(err => {
                    alert("Błąd startu skanera: " + err);
                });
            } else {
                alert("Brak kamer.");
            }
        })
        .catch(err => alert("Błąd pobierania kamer: " + err));
});
