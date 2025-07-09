<x-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-3xl mx-auto p-4">
        <h1 class="text-xl font-semibold mb-4 text-white">
            Nowe zamówienie produkcyjne dla: {{ $automat ? $automat->nazwa : '---' }}
        </h1>

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

                    <input type="number" name="produkty[0][ilosc]" min="1" max="3000" class="form-input w-24 text-black" placeholder="Ilość" required>

                    <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
                </div>
            </div>

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
        window._produkty = @json($produkty);
    </script>
    <script src="{{ asset('js/zamowienia-create.js') }}"></script>
</x-layout>