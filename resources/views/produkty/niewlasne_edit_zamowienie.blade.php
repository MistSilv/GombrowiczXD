<x-layout>
    <h1 class="text-xl sm:text-3xl font-bold mb-6 text-white">Edytuj ilości produktów dla zamówienia</h1>

    <label for="maxStan" class="block text-white font-semibold mb-1">Pokaż tylko produkty z ilością na stanie mniejszą niż:</label>
    <input type="number" id="maxStan" min="0"
        class="px-3 py-2 rounded border w-full sm:w-64 text-black"
        placeholder="Np. 200">

    <label for="filterNazwa" class="block text-white font-semibold mb-1 mt-4">Filtruj po nazwie produktu:</label>
    <input type="text" id="filterNazwa" 
        class="px-3 py-2 rounded border w-full sm:w-64 text-black"
        placeholder="Wpisz nazwę produktu">

    {{-- Tabela deficytów --}}
    <div class="p-4">
        <h2 class="text-xl font-bold mb-4">Deficyty Produktów Obcych</h2>
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="border px-4 py-2">Produkt</th>
                    <th class="border px-4 py-2">Wsady</th>
                    <th class="border px-4 py-2">Zamówienia</th>
                    <th class="border px-4 py-2">Na Stanie</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deficyty as $d)
                    <tr class="deficyt-row" data-deficyt="{{ $d['na_stanie'] }}">
                        <td class="border px-4 py-2 product-name cursor-pointer text-blue-600 underline">{{ $d['nazwa'] }}</td>
                        <td class="border px-4 py-2">{{ $d['wsady'] }}</td>
                        <td class="border px-4 py-2">{{ $d['zamowienia'] }}</td>
                        <td class="border px-4 py-2 
                            {{ $d['na_stanie'] < 0 ? 'text-red-600 font-bold' : 'text-green-700' }}">
                            {{ $d['na_stanie'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Formularz zamówień - pusta tabela --}}
    <form action="{{ route('produkty.zamowienie.zapisz') }}" method="POST" class="space-y-4" id="zamowienieForm">
        @csrf
        <input type="hidden" name="zamowienieId" value="{{ $zamowienieId ?? '' }}">
        <input type="hidden" name="wyslij_email" id="wyslijEmail" value="0">

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded shadow text-sm sm:text-base" id="produkty-lista">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-2 sm:px-6 text-left font-bold text-gray-700">Nazwa produktu</th>
                        <th class="py-3 px-2 sm:px-6 text-left font-bold text-gray-700">Ilość</th>
                        <th class="py-3 px-2 sm:px-6"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- start puste --}}
                </tbody>
            </table>
        </div>

        <button type="button" id="dodaj-produkt"
            class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
            + Dodaj produkt
        </button>

        <div class="mt-6 flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-4">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded transition duration-300 w-full sm:w-auto"
                onclick="if(confirm('Czy na pewno chcesz zapisać i wysłać email?')) { document.getElementById('wyslijEmail').value = '1'; return true; } return false;">
                <i class="fas fa-paper-plane mr-2"></i>Zapisz i wyślij email
            </button>

            <a href="{{ url('/welcome') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300 w-full sm:w-auto">
                <i class="fas fa-arrow-left mr-2"></i>Powrót
            </a>
        </div>
    </form>

    <script>
        window._produkty = @json($produkty);

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
                    <input type="number" min="0" max="3000" step="1" value="${ilosc || 0}" class="border rounded px-2 py-1 w-full max-w-[6rem] sm:w-32 text-black" required>
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
    </script>
</x-layout>
