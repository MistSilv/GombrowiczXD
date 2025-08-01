window.isFirmoweWifi = 0

function checkNetworkType() {
    fetch('/api/network/check')
        .then(res => res.json())
        .then(data => {
            window.isFirmoweWifi = data.connection_type === 'wifi_firmowe' ? 1 : 0;
            if (window.isFirmoweWifi) {
                console.log('Połączono z siecią firmową WiFi');
            } else {
                console.log('Nie jesteś połączony z siecią firmową WiFi');
            }
        })
        .catch(() => window.isFirmoweWifi = 0);
}
document.addEventListener('DOMContentLoaded', checkNetworkType);



window._produkty = JSON.parse(document.getElementById('produkty-data').textContent);

let index = 0;
//dodaje do tabeli tej na dole dynamicznej
function aktualizujDostepneProdukty() {
    const uzyteProdukty = new Set();
    document.querySelectorAll('#produkty-lista tbody tr').forEach(tr => {
        const pid = parseInt(tr.getAttribute('data-produkt-id'));
        if(pid) uzyteProdukty.add(pid);
    });

    document.querySelectorAll('#produkty-lista tbody tr').forEach(tr => {
        const select = tr.querySelector('select');
        if(!select) return; // w naszej wersji nie ma selectów

        const currentValue = select.value;
        select.innerHTML = '<option value="">-- wybierz produkt --</option>';
        window._produkty.forEach(p => {
            if (!uzyteProdukty.has(p.id) || p.id === parseInt(currentValue)) {
                const selected = p.id === parseInt(currentValue) ? 'selected' : '';
                select.innerHTML += `<option value="${p.id}" ${selected}>${p.tw_nazwa}</option>`;
            }
        });
    });

    const btnDodaj = document.getElementById('dodaj-produkt');
    if (btnDodaj) {
        const iloscDostepnych = window._produkty.length;
        btnDodaj.disabled = uzyteProdukty.size >= iloscDostepnych;
        btnDodaj.classList.toggle('opacity-50', btnDodaj.disabled);
        btnDodaj.classList.toggle('cursor-not-allowed', btnDodaj.disabled);
    }
    return uzyteProdukty;
}

function dodajWiersz(produktId = '', ilosc = '') {
    const tbody = document.querySelector('#produkty-lista tbody');
    const uzyteProdukty = aktualizujDostepneProdukty();

    // jeśli produkt już jest dodany, nie dodaj duplikatu
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

    // focus i select inputa ilości
    const inputIlosc = tr.querySelector('input[type="number"]');
    if(inputIlosc) {
        inputIlosc.focus();
        inputIlosc.select();
    }

    index++;
    aktualizujDostepneProdukty();
}

function setQuantity(produktId, ilosc) {
    const rows = document.querySelectorAll('#produkty-lista tbody tr');
    let found = false;

    for (const row of rows) {
        if (parseInt(row.getAttribute('data-produkt-id')) === produktId) {
            const input = row.querySelector('input[type="number"]');
            if (input) {
                // Dodajemy ilość do istniejącej wartości
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
}



// Usuwanie wiersza
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        aktualizujDostepneProdukty();
    }
});

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('product-name')) {
        const nazwa = e.target.textContent.trim();
        const produkt = window._produkty.find(p => p.tw_nazwa === nazwa);
        if (produkt) {
            setQuantity(produkt.id, 1);
        }
    }
});
// --- WYSZUKIWARKA PRODUKTÓW DO DODANIA ---
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
        if(pid) uzyteProdukty.add(pid);
    });

    const matches = window._produkty.filter(p => 
        p.tw_nazwa.toLowerCase().includes(val) &&
        !uzyteProdukty.has(p.id)
    ).slice(0, 10);

    showSuggestions(matches);
});

suggestionsList.addEventListener('click', (e) => {
    if (e.target.tagName === 'LI') {
        const produktId = parseInt(e.target.dataset.produktId);
        setQuantity(produktId, 0);
        clearSuggestions();
        searchInput.value = '';
    }
});

document.addEventListener('click', (e) => {
    if (e.target !== searchInput && e.target.parentNode !== suggestionsList) {
        clearSuggestions();
    }
});

// Obsługa filtrów deficytów
window.addEventListener('DOMContentLoaded', () => {
    aktualizujDostepneProdukty();

});

document.addEventListener('livewire:load', function () {
    Livewire.on('produktClicked', function (produktId) {
        const produkt = window._produkty.find(p => p.id === produktId);
        if (produkt) {
            setQuantity(produkt.id, 0);
        }
    });
});
/*
function handleEmailSend() {
    const checkbox = document.getElementById('WyslijMail');
    const wyslijEmailInput = document.getElementById('wyslijEmail');

    if (checkbox && checkbox.checked) {
        // Switch włączony => NIE wysyłamy maila
        wyslijEmailInput.value = '1';
        return confirm('Czy na pewno chcesz zapisać i wysłać email? Sklep');
    } else {
        // Switch wyłączony => wysyłamy maila
        if (confirm('Czy na pewno chcesz zapisać i wysłać email? Import')) {
            wyslijEmailInput.value = '0';
            return true;
        }
        return false;
    }
}

function handleEanSearch() {
    const eanInput = document.getElementById('product-search-ean');
    const decodedText = eanInput.value.trim();
    if (!decodedText) {
        alert('Wpisz kod EAN/PLU');
        return;
    }

    const token = $('meta[name="csrf-token"]').attr('content');

    if (window.isFirmoweWifi === 1) {
        console.log("Jesteś w sieci firmowej – wysyłam zapytanie do kontrolera backendowego");

        fetch('/api/check-ean-firmowe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ kod_ean: decodedText })
        })
        .then(res => {
            if (!res.ok) throw new Error('Błąd odpowiedzi z backendu');
            return res.json();
        })
        .then(response => {
            if (!response.success) {
                alert(response.message || "Nie znaleziono produktu");
                return;
            }

            const dane = response.wyniki;

            if (!Array.isArray(dane) || dane.length === 0) {
                alert("Brak danych z backendu (firmowe API).");
                return;
            }

            dane.forEach(item => {
                console.log("🧾 Nazwa towaru:", item.nazwa_towaru);
                console.log("📦 Jednostka miary:", item.jm);
                console.log("🆔 ID Abaco:", item.idabaco);
                console.log("📑 Kody PLU:", item.kody_plu.join(', '));

                const qty = prompt(`Podaj ilość dla produktu: ${item.nazwa_towaru}`, "1");

                if (qty && !isNaN(qty) && parseInt(qty) > 0) {
                    const produktId = parseInt(item.idabaco);
                    const produktNazwa = item.nazwa_towaru;

                    // Dodaj produkt do listy dostępnych, jeśli go tam nie ma
                    let istnieje = window._produkty.find(p => p.id === produktId);
                    if (!istnieje) {
                        window._produkty.push({
                            id: produktId,
                            tw_nazwa: produktNazwa
                        });
                    }

                    // Dodaj do tabeli lub zaktualizuj ilość
                    setQuantity(produktId, parseInt(qty));
                } else {
                    alert("Nieprawidłowa ilość.");
                }
            });
        })
        .catch(err => {
            console.error("❌ Błąd:", err.message);
            alert("Błąd po stronie backendu (firmowe API).");
        });

        eanInput.value = '';
        return;
    }

    // 🔄 ZACHOWANIE DOMYŚLNE (poza siecią firmową)
    fetch('/api/check-ean', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ kod_ean: decodedText })
    })
        .then(res => {
            if (!res.ok) return res.json().then(err => { throw err });
            return res.json();
        })
        .then(data => {
            const qty = prompt(`Podaj ilość dla produktu: ${data.produkt.tw_nazwa}`, "1");
            if (qty && !isNaN(qty) && parseInt(qty) > 0) {
                setQuantity(data.produkt.id, parseInt(qty));
            } else {
                alert("Nieprawidłowa ilość.");
            }
        })
        .catch(err => {
            alert(err.message || 'Błąd przy sprawdzaniu kodu.');
            $('#scan-result').text('');
        });

    eanInput.value = '';
}
*/
function handleEanSearch() {
    //window.isFirmoweWifi =0;
    const eanInput = document.getElementById('product-search-ean');
    const decodedText = eanInput.value.trim();
    
    if (!decodedText) {
        alert('Wpisz kod EAN/PLU');
        return;
    }

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
        if (!response.ok) throw new Error('Błąd sieci');
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Nie znaleziono produktu');
        }

        const produkt = data.produkt;
        const qty = prompt(`Podaj ilość dla produktu: ${produkt.tw_nazwa}`, "1");

        if (!qty || isNaN(qty) || parseInt(qty) <= 0) {
            throw new Error('Nieprawidłowa ilość');
        }

        // Dodaj produkt do globalnej listy jeśli nie istnieje
        let lokalnyProdukt = window._produkty.find(p => p.tw_idabaco === produkt.tw_idabaco);
        
        // W funkcji handleEanSearch:
        if (!lokalnyProdukt) {
            lokalnyProdukt = {
                id: Math.max(0, ...window._produkty.map(p => p.id)) + 1,
                tw_nazwa: produkt.tw_nazwa,
                tw_idabaco: produkt.tw_idabaco,
                ean_codes: produkt.ean_codes || [] // Upewnij się, że to pole jest ustawione
            };
            window._produkty.push(lokalnyProdukt);
        }

        setQuantity(lokalnyProdukt.id, parseInt(qty));
        eanInput.value = '';
    })
    .catch(error => {
        console.error('Błąd:', error);
        alert(error.message);
    });
    
}


document.getElementById('dodaj-ean').addEventListener('click', handleEanSearch);



// --- EAN Scanner ---
const scanner = new Html5Qrcode("reader");
let isScanning = false;

function onScanSuccess(decodedText) {
    console.log("Zeskanowano:", decodedText);

    scanner.stop().then(() => {
        isScanning = false;
        $('#reader').hide();
        $('#scan-result').text(`Zeskanowano: ${decodedText}`);

        // Użyj tej samej logiki co w handleEanSearch
        handleScannedEan(decodedText);
    }).catch(err => {
        console.error('Błąd zatrzymania skanera:', err);
    });
}

// Wspólna funkcja do obsługi zeskanowanego/samodzielnie wprowadzonego kodu
function handleScannedEan(decodedText) {
    //window.isFirmoweWifi =0;
    const fetchUrl = window.isFirmoweWifi === 1 ? '/api/check-ean-firmowe' : '/api/check-ean';

    fetch(fetchUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ kod_ean: decodedText })
    })
    .then(response => {
        if (!response.ok) throw new Error('Brak Produktu');
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Nie znaleziono produktu');
        }

        const produkt = data.produkt;
        const qty = prompt(`Podaj ilość dla produktu: ${produkt.tw_nazwa}`, "1");

        if (!qty || isNaN(qty) || parseInt(qty) <= 0) {
            throw new Error('Nieprawidłowa ilość');
        }

        // Dodaj produkt do globalnej listy jeśli nie istnieje
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

// Pozostała część kodu skanera bez zmian
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

// Zaktualizowana funkcja handleEanSearch (teraz używa tej samej logiki)
function handleEanSearch() {
    const eanInput = document.getElementById('product-search-ean');
    const decodedText = eanInput.value.trim();
    
    if (!decodedText) {
        alert('Wpisz kod EAN/PLU');
        return;
    }

    handleScannedEan(decodedText);
    eanInput.value = '';
}

document.getElementById('dodaj-ean').addEventListener('click', handleEanSearch);