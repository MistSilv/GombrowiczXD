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

            <!-- Lista produktów -->
            <div id="produkty-lista">
                <div class="flex items-center gap-2 mb-2 produkt-item">
                    <input
                        type="text"
                        name="produkty[0][tw_nazwa]"
                        class="form-input w-full autocomplete-input text-black"
                        placeholder="Wpisz nazwę produktu"
                        required
                        autocomplete="off"
                    >
                    <input type="hidden" name="produkty[0][produkt_id]" class="produkt-id-hidden">
                    <input
                        type="number"
                        name="produkty[0][ilosc]"
                        min="1" max="3000"
                        class="form-input w-24 text-black"
                        placeholder="Ilość"
                        required
                        value="1"
                    >
                    <button type="button" class="bg-red-600 text-white rounded px-3 py-1 hover:bg-red-700 transition remove-item">✕</button>
                </div>
            </div>

            

            <!-- Przyciski -->
            <button type="button" id="dodaj-produkt" class="mt-2 mb-4 bg-blue-500 text-white px-3 py-1 rounded">
                + Dodaj produkt
            </button>

            <div class="mt-4">
                <button 
                    type="submit" 
                    onclick="return confirm('Czy na pewno chcesz potwierdzić zamówienie?')"
                    class="bg-green-600 text-white px-4 py-2 rounded">
                        Złóż zamówienie
                </button>
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
                    <a href="{{ route('wsady.create', ['automat_id' => $automat->id]) }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                        Powrót
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script>
        // Tylko produkty is_wlasny = true
        window._produkty = @json($produkty->filter(fn($p) => $p->is_wlasny));
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/zamowienia-create.js') }}"></script>
</x-layout>
