window._produkty = JSON.parse(document.getElementById('produkty-data').textContent);

let index = 0;

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
    for (const row of rows) {
        if (parseInt(row.getAttribute('data-produkt-id')) === produktId) {
            const input = row.querySelector('input[type="number"]');
            if(input) {
                input.value = ilosc;
                input.focus();
                input.select();
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }
    }
    // jeśli nie znaleziono, dodaj nowy wiersz i ustaw ilość
    dodajWiersz(produktId, ilosc);
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

// Usuwanie wiersza
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
            setQuantity(produkt.id, 0);
        }
    });
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
    filtrujDeficyty();
    document.getElementById('maxStan').addEventListener('input', filtrujDeficyty);
    document.getElementById('filterNazwa').addEventListener('input', filtrujDeficyty);
});
