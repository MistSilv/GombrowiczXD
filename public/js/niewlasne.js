window._produkty = JSON.parse(document.getElementById('produkty-data').textContent);

let index = 0;

function aktualizujDostepneProdukty() {
    const uzyteProdukty = new Set();
    document.querySelectorAll('#produkty-lista select').forEach(select => {
        if (select.value) uzyteProdukty.add(parseInt(select.value));
    });

    document.querySelectorAll('#produkty-lista select').forEach(select => {
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
        return;
    }

    let options = '<option value="">-- wybierz produkt --</option>';
    window._produkty.forEach(p => {
        if (!uzyteProdukty.has(p.id) || p.id === parseInt(produktId)) {
            const selected = p.id === parseInt(produktId) ? 'selected' : '';
            options += `<option value="${p.id}" ${selected}>${p.tw_nazwa}</option>`;
        }
    });

    const tr = document.createElement('tr');
    tr.setAttribute('data-produkt-id', produktId || '');
    tr.innerHTML = `
        <td class="py-3 px-2 sm:px-6 w-full">
            <select class="w-full border rounded px-2 py-1" required ${produktId ? 'disabled' : ''}>
                ${options}
            </select>
            <input type="hidden" name="ilosci[${produktId}]" value="${ilosc || 0}">
        </td>
        <td class="py-3 px-2 sm:px-6">
            <input type="number" min="0" max="3000" step="1" value="${ilosc || 0}"
            class="border rounded px-3 py-2 w-full sm:w-32 text-right text-base sm:text-sm text-black bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            required>
        </td>
        <td class="py-3 px-2 sm:px-6 text-right">
            <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded transition remove-row">✕</button>
        </td>
    `;

    tbody.appendChild(tr);

    const select = tr.querySelector('select');
    const input = tr.querySelector('input[type="number"]');
    const hiddenInput = tr.querySelector('input[type="hidden"]');

    // Obsługa wyboru produktu w select
    if (!produktId) {
        select.addEventListener('change', () => {
            const val = select.value;
            if (!val) return;
            tr.setAttribute('data-produkt-id', val);
            hiddenInput.name = `ilosci[${val}]`;
            hiddenInput.value = input.value || 0;
            select.disabled = true;
            aktualizujDostepneProdukty();
            input.focus();
            input.select();
        });
    }

    // Aktualizacja ukrytego inputa z ilością
    input.addEventListener('input', () => {
        hiddenInput.value = input.value;
    });

    index++;
    aktualizujDostepneProdukty();
}

function setQuantity(produktId, ilosc) {
    const rows = document.querySelectorAll('#produkty-lista tbody tr');
    for (const row of rows) {
        if (parseInt(row.getAttribute('data-produkt-id')) === produktId) {
            const input = row.querySelector('input[type="number"]');
            if(input) {
                input.value = ilosc;
                input.focus();
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            const hiddenInput = row.querySelector('input[type="hidden"]');
            if(hiddenInput) hiddenInput.value = ilosc;
            return;
        }
    }
    // jeśli nie znaleziono, dodaj nowy wiersz i ustaw ilość
    dodajWiersz(produktId, ilosc);
    setTimeout(() => {
        const rowsNowe = document.querySelectorAll('#produkty-lista tbody tr');
        for (const row of rowsNowe) {
            if (parseInt(row.getAttribute('data-produkt-id')) === produktId) {
                const input = row.querySelector('input[type="number"]');
                if (input) {
                    input.focus();
                    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                break;
            }
        }
    }, 100);
}

function filtrujDeficyty() {
    const maxStanInput = document.getElementById('maxStan');
    const filterNazwaInput = document.getElementById('filterNazwa');

    const maxStan = maxStanInput.value.trim() === '' ? null : parseInt(maxStanInput.value);
    const filterNazwa = filterNazwaInput.value.trim().toLowerCase();

    document.querySelectorAll('.deficyt-row').forEach(row => {
        const naStanie = parseInt(row.getAttribute('data-deficyt')) || 0;
        const nazwa = row.querySelector('.product-name').textContent.toLowerCase();

        const pokazPoStanie = (maxStan === null || naStanie < maxStan);
        const pokazPoNazwie = (filterNazwa === '' || nazwa.includes(filterNazwa));

        if (pokazPoStanie && pokazPoNazwie) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        aktualizujDostepneProdukty();
    }
});

// Dodawanie pustego wiersza na kliknięcie przycisku
document.getElementById('dodaj-produkt').addEventListener('click', () => dodajWiersz());

// Dodaj produkt do zamówienia po kliknięciu nazwy z tabeli deficytów
document.querySelectorAll('.product-name').forEach(el => {
    el.addEventListener('click', () => {
        const nazwa = el.textContent.trim();
        const produkt = window._produkty.find(p => p.tw_nazwa === nazwa);
        if (produkt) {
            dodajWiersz(produkt.id, 0);

            setTimeout(() => {
                const rows = document.querySelectorAll('#produkty-lista tbody tr');
                for (const row of rows) {
                    if (parseInt(row.getAttribute('data-produkt-id')) === produkt.id) {
                        const inputIlosc = row.querySelector('input[type="number"]');
                        if (inputIlosc) {
                            inputIlosc.focus();
                            inputIlosc.select();
                        }
                        break;
                    }
                }
            }, 100);
        }
    });
});

window.addEventListener('DOMContentLoaded', () => {
    aktualizujDostepneProdukty();
    filtrujDeficyty();
    document.getElementById('maxStan').addEventListener('input', filtrujDeficyty);
    document.getElementById('filterNazwa').addEventListener('input', filtrujDeficyty);
});
