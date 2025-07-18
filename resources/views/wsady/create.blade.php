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

             <button type="button" id="dodaj-produkt" class="mt-2 mb-4 bg-blue-500 text-white px-3 py-1 rounded">
                + Dodaj produkt
            </button>
            
            <button
                type="submit"
                onclick="return confirm('Czy na pewno chcesz dodać wsad?')"
                class="px-4 py-2 rounded bg-green-700 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2"
            >
                Dodaj wsad
            </button>

            <div class="mt-6 flex flex-col md:flex-row md:items-center">
                @if($automat)
                    <a href="{{ route('zamowienia.create', ['automat_id' => $automat->id]) }}" class="px-4 py-2 rounded bg-slate-800 hover:bg-red-900 text-white font-semibold transition ml-2 mt-2">
                        Wprowadź zamówienie
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script>
        window._produkty = @json($produkty);
    </script>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/wsady-create.js') }}"></script>
</x-layout>
