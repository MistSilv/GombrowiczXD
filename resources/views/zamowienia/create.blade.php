<x-layout>
    <div class="max-w-3xl mx-auto p-4">
        <h1 class="text-xl font-semibold mb-4 text-white">
            Nowe zamówienie dla: {{ $automat ? $automat->nazwa : '---' }}
        </h1>

        <form action="{{ route('zamowienia.store') }}" method="POST">
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

                    <button type="button" class=" bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
                </div>
            </div>

            <button type="button" id="dodaj-produkt" class="mt-2 mb-4 bg-blue-500 text-white px-3 py-1 rounded">
                + Dodaj produkt
            </button>

            <div class="mt-4">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
                    Złóż zamówienie
                </button>
            </div>
            <div class="mt-6">
                @if($automat)
                    <a href="{{ route('straty.create', ['automat_id' => $automat->id]) }}" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
                        Wprowadź straty
                    </a>
                    @auth
                    @if(!auth()->user()->isProdukcja())
                    <a href="{{ route('zamowienia.index', ['automat_id' => $automat->id]) }}" class="ml-2 bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
                        Lista zamówień tego automatu
                    </a>
                    @endif
                    @endauth
                    @auth
                    @if(auth()->user()->isProdukcja())
                        <a href="{{ route('zamowienia.index') }}"
                        class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold  transition ml-2">
                        Zamówienia
                        </a>
                    @endif
                    @endauth
                    <a href="{{ route('straty.index') }}"
                        class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold  transition ml-2">
                        Straty
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script>
        let index = 1;
        const produkty = @json($produkty);

        document.getElementById('dodaj-produkt').addEventListener('click', function () {
            const container = document.getElementById('produkty-lista');

            let options = '<option value="">-- wybierz produkt --</option>';
            produkty.forEach(function(produkt) {
                options += `<option value="${produkt.id}">${produkt.tw_nazwa}</option>`;
            });

            const newItem = document.createElement('div');
            newItem.classList.add('flex', 'items-center', 'gap-2', 'mb-2', 'produkt-item');
            newItem.innerHTML = `
                <select name="produkty[${index}][produkt_id]" class="form-select w-full" required>
                    ${options}
                </select>
                <input type="number" name="produkty[${index}][ilosc]" min="1" class="form-input w-24 text-black" placeholder="Ilość" required>
                <button type="button" class=" bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
            `;
            container.appendChild(newItem);
            index++;
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-item')) {
                e.target.closest('.produkt-item').remove();
            }
        });
    </script>
</x-layout>