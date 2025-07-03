<x-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-3xl mx-auto p-4">
        <h1 class="text-xl font-semibold mb-4 text-white">
            Nowe dodanie produktów dla: {{ $automat ? $automat->nazwa : '---' }}
        </h1>

        <div class="mb-6">
            <h2 class="text-white font-bold mb-2">Skanuj kod EAN</h2>
            
            <button id="start-scan" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                Rozpocznij skanowanie
            </button>

            <div id="reader" style="width: 300px; display: none;"></div>
            <div id="scan-result" class="mt-2 text-white"></div>
        </div>

        <form action="{{ route('wsady.store') }}" method="POST">
            @csrf

            @if($automat)
                <input type="hidden" name="automat_id" value="{{ $automat->id }}">
            @endif

            <div id="produkty-lista">
                <div class="flex items-center gap-2 mb-2 produkt-item">
                    <select name="produkty[0][produkt_id]" class="form-select w-full" required>
                        <option value="">-- wybierz produkt --</option>
                        @foreach($produkty as $produkt)
                            <option value="{{ $produkt->id }}">{{ $produkt->tw_nazwa }}</option>
                        @endforeach
                    </select>

                    <input type="number" name="produkty[0][ilosc]" min="1" class="form-input w-24 text-black" placeholder="Ilość" required>

                    <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
                </div>
            </div>

            <div class="mb-4 relative">
                <input type="text" id="szukaj-produkt" placeholder="Wpisz nazwę produktu" class="form-input w-full text-black mb-2" />
                <ul id="lista-podpowiedzi" class="absolute z-10 bg-white text-black max-h-40 overflow-auto border w-full hidden"></ul>
            </div>

            <button type="button" id="dodaj-produkt" class="mt-2 mb-4 bg-blue-500 text-white px-3 py-1 rounded">
                + Dodaj produkt
            </button>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 rounded bg-green-700 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                    Dodaj wsad
                </button>
            </div>

            <div class="mt-6 flex flex-col md:flex-row md:items-center">
                @if($automat)
                    <a href="{{ route('zamowienia.create', ['automat_id' => $automat->id]) }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                        Wprowadź zamówienie
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    let index = 1;
    const produkty = @json($produkty);

    const produktyMap = new Map();
    produkty.forEach(p => produktyMap.set(p.id, p.tw_nazwa));

    // Funkcja do focusowania i zaznaczania pola ilości obok selecta
    function focusIloscDlaSelecta(selectElem) {
        const produktItem = selectElem.closest('.produkt-item');
        if (!produktItem) return;
        const inputIlosc = produktItem.querySelector('input[type="number"]');
        if (inputIlosc) {
            inputIlosc.focus();
            setTimeout(() => inputIlosc.select(), 100);
        }
    }

    // Dodawanie produktu ręcznie
    document.getElementById('dodaj-produkt').addEventListener('click', () => {
        dodajProduktDoListy();
    });

    // Usuwanie produktu
    document.addEventListener('click', e => {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.produkt-item').remove();
        }
    });

    // Skaner
    const scanner = new Html5Qrcode("reader");
    let isScanning = false;

    function onScanSuccess(decodedText) {
        scanner.stop().then(() => {
            isScanning = false;
            $('#reader').hide();
            $('#scan-result').text(`Zeskanowano: ${decodedText}`);

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/api/check-ean', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ kod_ean: decodedText })
            })
            .then(res => {
                if (!res.ok) return res.json().then(err => { throw err });
                return res.json();
            })
            .then(data => {
                const ilosc = prompt(`Podaj ilość dla produktu: ${data.produkt.tw_nazwa}`, "1");
                if (ilosc && !isNaN(ilosc) && parseInt(ilosc) > 0) {
                    dodajProduktDoListy(data.produkt.id, data.produkt.tw_nazwa, parseInt(ilosc));
                    // focus jest ustawiany w dodajProduktDoListy
                } else {
                    alert("Nieprawidłowa ilość.");
                }
            })
            .catch(err => alert(err.message || 'Błąd przy sprawdzaniu kodu.'));
        });
    }

    document.getElementById('start-scan').addEventListener('click', () => {
        if (isScanning) return;
        Html5Qrcode.getCameras().then(devices => {
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
        }).catch(err => alert("Błąd pobierania kamer: " + err));
    });

    // Funkcja dodająca produkt do listy
    function dodajProduktDoListy(produktId = null, nazwaProduktu = '', ilosc = 1) {
        const container = document.getElementById('produkty-lista');

        // Jeśli produkt już jest na liście, tylko zwiększ ilość
        if (produktId) {
            const istniejącySelect = [...container.querySelectorAll('select')].find(s => s.value == produktId);
            if (istniejącySelect) {
                const inputIlosc = istniejącySelect.closest('.produkt-item').querySelector('input[type="number"]');
                inputIlosc.value = parseInt(inputIlosc.value) + ilosc;
                inputIlosc.focus();
                setTimeout(() => inputIlosc.select(), 100);
                return;
            }
        }

        let options = '<option value="">-- wybierz produkt --</option>';
        produkty.forEach(p => {
            const selected = (produktId && p.id == produktId) ? 'selected' : '';
            options += `<option value="${p.id}" ${selected}>${p.tw_nazwa}</option>`;
        });

        const newItem = document.createElement('div');
        newItem.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'produkt-item');
        newItem.innerHTML = `
            <select name="produkty[${index}][produkt_id]" class="form-select w-full" required>
                ${options}
            </select>
            <input type="number" name="produkty[${index}][ilosc]" min="1" value="${ilosc}" class="form-input w-24 text-black" placeholder="Ilość" required>
            <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
        `;

        container.appendChild(newItem);

        // Podpinamy event change do nowego selecta, żeby po wyborze focus był na ilości
        const selectNowy = newItem.querySelector('select');
        selectNowy.addEventListener('change', () => focusIloscDlaSelecta(selectNowy));

        // Ustawiamy focus na input ilości
        const inputIloscNowy = newItem.querySelector('input[type="number"]');
        if (inputIloscNowy) {
            inputIloscNowy.focus();
            setTimeout(() => inputIloscNowy.select(), 100);
        }

        index++;
    }

    // Podpinamy event change do istniejących selectów na starcie strony
    document.querySelectorAll('#produkty-lista select').forEach(select => {
        select.addEventListener('change', () => focusIloscDlaSelecta(select));
    });

    // Podpowiedzi przy wpisywaniu nazwy produktu
    const szukajInput = document.getElementById('szukaj-produkt');
    const listaPodpowiedzi = document.getElementById('lista-podpowiedzi');

    szukajInput.addEventListener('input', () => {
        const val = szukajInput.value.toLowerCase();
        if (val.length < 2) {
            listaPodpowiedzi.style.display = 'none';
            listaPodpowiedzi.innerHTML = '';
            return;
        }

        const dopasowane = produkty.filter(p => p.tw_nazwa.toLowerCase().includes(val));
        if (!dopasowane.length) {
            listaPodpowiedzi.style.display = 'none';
            listaPodpowiedzi.innerHTML = '';
            return;
        }

        listaPodpowiedzi.innerHTML = dopasowane.map(p => `<li data-id="${p.id}" class="cursor-pointer px-2 py-1 hover:bg-gray-200">${p.tw_nazwa}</li>`).join('');
        listaPodpowiedzi.style.display = 'block';
    });

    listaPodpowiedzi.addEventListener('click', e => {
        if (e.target.tagName.toLowerCase() === 'li') {
            const id = e.target.getAttribute('data-id');
            const nazwa = e.target.textContent;
            dodajProduktDoListy(id, nazwa);
            szukajInput.value = '';
            listaPodpowiedzi.style.display = 'none';
            listaPodpowiedzi.innerHTML = '';
        }
    });

    document.addEventListener('click', e => {
        if (!listaPodpowiedzi.contains(e.target) && e.target !== szukajInput) {
            listaPodpowiedzi.style.display = 'none';
            listaPodpowiedzi.innerHTML = '';
        }
    });
    </script>
</x-layout>
