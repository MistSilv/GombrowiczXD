<!-- strona do tworzenia zamówienia -->
<x-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-3xl mx-auto p-4">
        <h1 class="text-xl font-semibold mb-4 text-white">
            Nowe zamówienie dla: {{ $automat ? $automat->nazwa : '---' }}
        </h1>

        <!-- start skanowania kodu EAN -->
        <div class="mb-6">
        <h2 class="text-white font-bold mb-2">Skanuj kod EAN</h2>
        
        <button id="start-scan" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
            Rozpocznij skanowanie
        </button>

        <div id="reader" style="width: 300px; display: none;"></div>
        <div id="scan-result" class="mt-2 text-white"></div>
        </div>


        <form action="{{ route('zamowienia.store') }}" method="POST">
            @csrf

            <!-- ukryte pole z ID automatu, jeśli jest dostępne -->
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
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
                    Złóż zamówienie
                </button>
            </div>

            <div class="mt-6 flex flex-col md:flex-row md:items-center">
                @if($automat)
                    <a href="{{ route('straty.create', ['automat_id' => $automat->id]) }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                        Wprowadź straty
                    </a>
                    @auth
                    @if(!auth()->user()->isProdukcja()) <!--widok dla każdego oprócz produkcji -->
                        <a href="{{ route('zamowienia.index', ['automat_id' => $automat->id]) }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                            Lista zamówień tego automatu
                        </a>
                    @endif 
                    @endauth
                    @auth
                    @if(!auth()->user()->isSerwis()) <!--widok dla każdego oprócz serwisanta-->
                        <a href="{{ route('zamowienia.index') }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                            Zamówienia
                        </a>
                    @endif
                    @endauth
                    <a href="{{ route('straty.index') }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                        Straty
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
      let index = 1; // Lgeneruje unikalną nazwę dla każdego produktu

        const produkty = @json($produkty); // Lista produktów 

        const produktyMap = new Map();
        // mapa dla wysukiwania produktów
        produkty.forEach(p => produktyMap.set(p.id, p.tw_nazwa));

        // dodawanie produktu
        document.getElementById('dodaj-produkt').addEventListener('click', function () {
            const container = document.getElementById('produkty-lista');

            // wybór produktu z listy
            let options = '<option value="">-- wybierz produkt --</option>';
            produkty.forEach(function(produkt) {
                options += `<option value="${produkt.id}">${produkt.tw_nazwa}</option>`;
            });

            // tworzenie nowego wierszu
            const newItem = document.createElement('div');
            newItem.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'produkt-item');
            newItem.innerHTML = `
                <select name="produkty[${index}][produkt_id]" class="form-select w-full" required>
                    ${options}
                </select>
                <input type="number" name="produkty[${index}][ilosc]" min="1" class="form-input w-24 text-black" placeholder="Ilość" required>
                <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
            `;
            container.appendChild(newItem);
            index++;
        });

        // usuwanie produktu
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-item')) {
                e.target.closest('.produkt-item').remove();
            }
        });

        // deklaracja skanera kodów
        const scanner = new Html5Qrcode("reader");
        let isScanning = false; // sprawdza czy skaner jest aktywny

        // jak kod działa to sie to uruchamia
        function onScanSuccess(decodedText) {
            scanner.stop().then(() => {
                isScanning = false;
                $('#reader').hide();
                $('#scan-result').text(`Zeskanowano: ${decodedText}`);

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // zapytania do API aby sprawdzić kod EAN
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
                    //szukanie produktu i dodawanie go do listy (pyta też o ilość)
                    const ilosc = prompt(`Podaj ilość dla produktu: ${data.produkt.tw_nazwa}`, "1");

                    if (ilosc !== null && !isNaN(ilosc) && parseInt(ilosc) > 0) {
                        dodajProduktDoListy(data.produkt.id, data.produkt.tw_nazwa, parseInt(ilosc));
                    } else {
                        alert("Produkt nie został dodany — podano nieprawidłową ilość.");
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert(err.message || 'Produkt nie znaleziony lub błąd podczas sprawdzania kodu.');
                });
            }).catch(err => {
                console.error("Błąd zatrzymywania skanera:", err);
            });
        }

        // przycisk do skanera
        document.getElementById('start-scan').addEventListener('click', () => {
            if (isScanning) return; // sprawdzanie czy już skanuje żeby nie powtarzać

            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    $('#reader').show();
                    // skaner tylnia kamera
                    scanner.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, onScanSuccess)
                        .then(() => { isScanning = true; })
                        .catch(err => { console.error("Błąd uruchamiania skanera:", err); });
                } else {
                    alert("Brak dostępnych kamer.");
                }
            }).catch(err => {
                console.error("Błąd pobierania kamer:", err);
            });
        });

        function dodajProduktDoListy(produktId, nazwaProduktu, ilosc = 1) {
            const container = document.getElementById('produkty-lista');
            
            // sprawdza czy produkt jest na liście
            const istniejący = [...container.querySelectorAll('select')].find(select => select.value == produktId);

            if (istniejący) {
                // tak, zwiększa ilość
                const inputIlosc = istniejący.closest('.produkt-item').querySelector('input[type="number"]');
                inputIlosc.value = parseInt(inputIlosc.value) + ilosc;
            } else {
                // nie, tworzy nowy wiersz
                const newItem = document.createElement('div');
                newItem.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'produkt-item');

                let options = `<option value="">-- wybierz produkt --</option>`;
                produkty.forEach(function (p) {
                    options += `<option value="${p.id}" ${p.id == produktId ? 'selected' : ''}>${p.tw_nazwa}</option>`;
                });

                newItem.innerHTML = `
                    <select name="produkty[${index}][produkt_id]" class="form-select w-full" required>
                        ${options}
                    </select>
                    <input type="number" name="produkty[${index}][ilosc]" min="1" value="${ilosc}" class="form-input w-24 text-black" placeholder="Ilość" required>
                    <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
                `;
                container.appendChild(newItem);
                index++;
            }
        }

        // uzupełnia pole wyszukiwania po nazwie
        const szukajInput = document.getElementById('szukaj-produkt');
        const listaPodpowiedzi = document.getElementById('lista-podpowiedzi');

        szukajInput.addEventListener('input', () => {
            const fraza = szukajInput.value.trim().toLowerCase();
            listaPodpowiedzi.innerHTML = '';

            if (!fraza) {
                listaPodpowiedzi.classList.add('hidden'); // ukrywa listę podpowiedzi
                return;
            }

            // wyszukiwarka po frazie
            const pasujaceProdukty = produkty.filter(p => p.tw_nazwa.toLowerCase().includes(fraza));

            if (pasujaceProdukty.length) {
                pasujaceProdukty.forEach(p => {
                    const li = document.createElement('li');
                    li.textContent = p.tw_nazwa;
                    li.classList.add('p-2', 'hover:bg-gray-200', 'cursor-pointer');
                    
                    // jak klikniesz to doda produkt do listy
                    li.addEventListener('click', () => {
                        dodajProduktDoListy(p.id, p.tw_nazwa);
                        szukajInput.value = '';
                        listaPodpowiedzi.innerHTML = '';
                        listaPodpowiedzi.classList.add('hidden');
                    });
                    listaPodpowiedzi.appendChild(li);
                });
                listaPodpowiedzi.classList.remove('hidden');
            } else {
                listaPodpowiedzi.classList.add('hidden');
            }
        });

        document.addEventListener('click', (e) => {
            if (!listaPodpowiedzi.contains(e.target) && e.target !== szukajInput) {
                listaPodpowiedzi.classList.add('hidden'); // ukrywa listę podpowiedzi
            }
        });
    </script>
</x-layout>
