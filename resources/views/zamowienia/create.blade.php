<x-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-3xl mx-auto p-4">
        <h1 class="text-xl font-semibold mb-4 text-white">
            Nowe zamówienie produkcyjne dla: {{ $automat ? $automat->nazwa : '---' }}
        </h1>

        <!-- Sekcja skanowania kodu EAN -->
        <!--
        <div class="mb-6">
            <h2 class="text-white font-bold mb-2">Skanuj kod EAN</h2>
            <button id="start-scan" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                Rozpocznij skanowanie
            </button>
            <div id="reader" style="width: 300px; display: none;"></div>
            <div id="scan-result" class="mt-2 text-white"></div>
        </div>
       -->
        <form action="{{ route('zamowienia.store') }}" method="POST">
            @csrf

            @if($automat)
                <input type="hidden" name="automat_id" value="{{ $automat->id }}">
            @endif

            <div id="produkty-lista">
                <div class="flex items-center gap-2 mb-2 produkt-item">
                    <select name="produkty[0][produkt_id]" class="form-select w-full produkty-select" required>
                        <option value="">-- wybierz produkt --</option>
                        @foreach($produkty as $produkt)
                            @if($produkt->is_wlasny)
                                <option value="{{ $produkt->id }}" data-is-wlasny="1">
                                    {{ $produkt->tw_nazwa }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    <input type="number" name="produkty[0][ilosc]" min="1" max="2147483647" class="form-input w-24 text-black" placeholder="Ilość" required>

                    <button type="button" class=" bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
                </div>
            </div>

            <!-- pole do wyszukiwania produktów po nazwie -->
            <div class="mb-4 relative">
                <input type="text" id="szukaj-produkt" placeholder="Wpisz nazwę produktu" class="form-input w-full text-black mb-2" />
                <ul id="lista-podpowiedzi" class="absolute z-10 bg-white text-black max-h-40 overflow-auto border w-full hidden"></ul>
            </div>

            <button type="button" id="dodaj-produkt-nazwa" class="bg-blue-500 text-white px-3 py-1 rounded hidden">
                Dodaj produkt po nazwie
            </button>

            <button type="button" id="dodaj-produkt" class="mt-2 mb-4 bg-blue-500 text-white px-3 py-1 rounded">
                + Dodaj produkt
            </button>

            <div class="mt-4">
                <form method="POST" action="{{ route('zloz.zamowienie') }}">
                    @csrf
                    <div class="mt-4">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
                            Złóż zamówienie
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-6 flex flex-col md:flex-row md:items-center">
                @if($automat)
                    <a href="{{ route('straty.create', ['automat_id' => $automat->id]) }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                        Wprowadź straty
                    </a>
                    @auth
                    @if(!auth()->user()->isProdukcja())
                        <a href="{{ route('zamowienia.index', ['automat_id' => $automat->id]) }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                            Lista zamówień tego automatu
                        </a>
                    @endif 
                    @endauth
                    @auth
                    @if(!auth()->user()->isSerwis())
                        <a href="{{ route('zamowienia.index') }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                            Zamówienia
                        </a>
                    @endif
                    @endauth
                    <a href="{{ route('straty.index') }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                        Straty
                    </a>
                    <a href="{{ route('wsady.index', ['automat_id' => $automat->id]) }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                        Powrót
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

    // Funkcja ustawiająca focus i zaznaczenie pola ilości po wybraniu produktu
    function focusIlePole(produktItem) {
        const iloscInput = produktItem.querySelector('input[type="number"]');
        if (iloscInput) {
            iloscInput.focus();
            iloscInput.select();
        }
    }

    // Dodawanie nowego produktu
    document.getElementById('dodaj-produkt').addEventListener('click', function() {
        const container = document.getElementById('produkty-lista');

        let options = '<option value="">-- wybierz produkt --</option>';
        produkty.forEach(function(produkt) {
            options += `<option value="${produkt.id}" data-is-wlasny="1">${produkt.tw_nazwa}</option>`;
        });

        const newItem = document.createElement('div');
        newItem.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'produkt-item');
        newItem.innerHTML = `
            <select name="produkty[${index}][produkt_id]" class="form-select w-full produkty-select" required>
                ${options}
            </select>
            <input type="number" name="produkty[${index}][ilosc]" min="1" class="form-input w-24 text-black" placeholder="Ilość" required>
            <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
        `;
        container.appendChild(newItem);
        index++;

        // Po dodaniu nowego produktu focus ustaw na select i ustaw listener zmiany dla focusu na input ilości
        const select = newItem.querySelector('select');
        select.focus();

        select.addEventListener('change', () => {
            focusIlePole(newItem);
        });
    });

    // Usuwanie produktu
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.produkt-item').remove();
        }
    });

    // Podpinanie eventów change do istniejących selectów (np. przy ładowaniu strony)
    document.querySelectorAll('.produkty-select').forEach(select => {
        select.addEventListener('change', (e) => {
            const produktItem = e.target.closest('.produkt-item');
            focusIlePole(produktItem);
        });
    });

    // Dodawanie produktu do listy (np. ze skanera lub wyszukiwarki)
    function dodajProduktDoListy(produktId, nazwaProduktu, ilosc = 1) {
        const container = document.getElementById('produkty-lista');

        let options = '<option value="">-- wybierz produkt --</option>';
        produkty.forEach(function(produkt) {
            options += `<option value="${produkt.id}" data-is-wlasny="1">${produkt.tw_nazwa}</option>`;
        });

        const newItem = document.createElement('div');
        newItem.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'produkt-item');
        newItem.innerHTML = `
            <select name="produkty[${index}][produkt_id]" class="form-select w-full produkty-select" required>
                ${options}
            </select>
            <input type="number" name="produkty[${index}][ilosc]" min="1" class="form-input w-24 text-black" placeholder="Ilość" required value="${ilosc}">
            <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
        `;
        container.appendChild(newItem);

        // Ustaw wartość selecta na wybrany produkt
        const selectNowy = newItem.querySelector('select');
        selectNowy.value = produktId;

        // Po dodaniu produktu focus ustaw na pole ilości i zaznacz tekst
        focusIlePole(newItem);

        // Podpięcie eventu change do nowego selecta
        selectNowy.addEventListener('change', () => {
            focusIlePole(newItem);
        });

        index++;
    }

    // Wyszukiwanie produktu po nazwie z podpowiedziami
    $(document).ready(function() {
        $('#szukaj-produkt').on('input', function() {
            const val = $(this).val().toLowerCase();
            const lista = $('#lista-podpowiedzi');
            lista.empty().hide();

            if (val.length < 2) {
                return;
            }

            const dopasowane = produkty.filter(p => p.tw_nazwa.toLowerCase().includes(val));

            if (dopasowane.length === 0) {
                return;
            }

            dopasowane.forEach(p => {
                const li = $('<li></li>')
                    .text(p.tw_nazwa)
                    .addClass('p-2 cursor-pointer hover:bg-gray-200')
                    .on('click', function() {
                        dodajProduktDoListy(p.id, p.tw_nazwa);
                        $('#szukaj-produkt').val('');
                        lista.hide();
                    });
                lista.append(li);
            });

            lista.show();
        });

        // Ukrywanie listy podpowiedzi przy kliknięciu poza nią
        $(document).on('click', function(event) {
            if (!$(event.target).closest('#szukaj-produkt, #lista-podpowiedzi').length) {
                $('#lista-podpowiedzi').hide();
            }
        });
    });

    // Skaner kodów kreskowych
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
                if (!res.ok) {
                    return res.json().then(err => { throw err });
                }
                return res.json();
            })
            .then(data => {
                const ilosc = prompt(`Podaj ilość dla produktu: ${data.produkt.tw_nazwa}`, "1");
                const iloscNum = Number(ilosc);
                if (iloscNum > 0) {
                    dodajProduktDoListy(data.produkt.id, data.produkt.tw_nazwa, iloscNum);
                }
            })
            .catch(err => {
                alert(err.message || 'Błąd podczas wyszukiwania produktu.');
            });

        }).catch(err => {
            console.error('Błąd zatrzymania skanera:', err);
        });
    }

    function onScanFailure(error) {
        // Ignorujemy błędy skanowania
    }

    $('#start-scan').on('click', function() {
        if (!isScanning) {
            $('#reader').show();
            scanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 250 },
                onScanSuccess,
                onScanFailure
            ).then(() => {
                isScanning = true;
            }).catch(err => {
                alert('Nie można rozpocząć skanowania: ' + err);
            });
        } else {
            scanner.stop().then(() => {
                isScanning = false;
                $('#reader').hide();
            }).catch(err => {
                alert('Nie można zatrzymać skanowania: ' + err);
            });
        }
    });
</script>
</x-layout>
