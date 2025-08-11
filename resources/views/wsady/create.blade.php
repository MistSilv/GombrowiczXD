 <x-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="max-w-3xl mx-auto p-6 bg-gray-900 rounded-2xl shadow-lg">
        <h1 class="text-2xl font-bold mb-6 text-white">
            Nowe dodanie produktów dla: <span class="text-rose-400">{{ $automat ? $automat->nazwa : '---' }}</span>
        </h1>

        <div class="mb-8 bg-gray-700 p-4 rounded-lg">
            <h2 class="text-lg font-semibold mb-4 text-white">Skanuj kod EAN</h2>
            
            <div class="flex flex-col sm:flex-row gap-4 items-start">
                <button id="start-scan" class="px-4 py-2 rounded-lg bg-blue-800 hover:bg-blue-600 text-white font-semibold transition-colors">
                    Rozpocznij skanowanie
                </button>

                <div id="reader" class="w-full sm:w-64 mx-auto sm:mx-0" style="display: none;"></div>
            </div>
            <div id="scan-result" class="mt-3 text-green-400 font-medium"></div>
        </div>

                {{-- Dropdown wyboru szablonu --}}
        @if(isset($aktywneSzablony) && $aktywneSzablony->count())
            <form method="GET" action="{{ route('wsady.create') }}" class="mb-6">
                @if($automat)
                    <input type="hidden" name="automat_id" value="{{ $automat->id }}">
                @endif
                <label for="wsad_template_id" class="block mb-2 text-white font-semibold">Wybierz szablon wsadu:</label>
                <select name="wsad_template_id" id="wsad_template_id" class="w-full max-w-xs p-2 rounded bg-gray-700 text-white">
                    <option value="">-- wybierz szablon --</option>
                    @foreach($aktywneSzablony as $template)
                        <option value="{{ $template->id }}" @if(request('wsad_template_id') == $template->id) selected @endif>{{ $template->nazwa }}</option>
                    @endforeach
                </select>
                <button type="submit" class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Załaduj szablon</button>
            </form>
        @endif

       <form action="{{ route('wsady.store') }}" method="POST" class="space-y-6">
        @csrf

        @if($automat)
            <input type="hidden" name="automat_id" value="{{ $automat->id }}">
        @endif

        <div class="space-y-4" id="produkty-lista">
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end produkt-item">
                <div class="w-full sm:w-auto flex-grow">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nazwa produktu</label>
                    <input
                        type="text"
                        name="produkty[0][tw_nazwa]"
                        class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white placeholder-gray-400"
                        placeholder="Wpisz nazwę produktu"
                        required
                        autocomplete="off">
                    <input type="hidden" name="produkty[0][produkt_id]" class="produkt-id-hidden">
                </div>
                
                <div class="flex items-end gap-2">
                    <div class="w-24">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Ilość</label>
                        <input
                            type="number"
                            name="produkty[0][ilosc]"
                            min="1" max="3000"
                            class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white"
                            placeholder="Ilość"
                            required
                            value="1">
                    </div>
                    
                    <button type="button" class="h-[42px] px-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors remove-item flex items-center justify-center">
                        Usuń
                    </button>
                </div>
            </div>
        </div>

            <div class="flex flex-wrap gap-4">
                <button type="button" id="dodaj-produkt" class="px-4 py-2 bg-green-700 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors flex items-center gap-1">
                    ✚ Dodaj produkt
                </button>
                
                <button
                    type="submit"
                    onclick="return confirm('Czy na pewno chcesz dodać wsad?')"
                    class="px-4 py-2 bg-green-800 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors">
                    Dodaj wsad
                </button>
            </div>

            @if($automat)
                <div class="pt-6 border-t border-gray-700">
                    <p class="mb-2 text-white font-semibold text-xl">🛒 Przejdź do zamawiania bułek</p>
                    <a href="{{ route('zamowienia.create', ['automat_id' => $automat->id]) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-800 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-colors">
                    Bułeczki 🍞
                    </a>
                </div>
            @endif
        </form>
    </div>

    <script>
        window._produkty = @json($produkty);
    </script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/wsady-create.js') }}"></script>

<script>
    window._initialProducts = @json(
        request('wsad_template_id')
            ? $wsadProdukty->map(function($p) {
                return [
                    'produkt_id' => is_object($p) ? $p->id : $p['produkt_id'],
                    'tw_nazwa' => is_object($p) ? $p->tw_nazwa : $p['tw_nazwa'],
                    'ilosc' => is_object($p) ? ($p->pivot->ilosc ?? 1) : $p['ilosc']
                ];
            })
            : []
    );
</script>
</x-layout>