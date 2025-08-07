<x-layout>
    <div class="max-w-3xl mx-auto p-6 bg-gray-900 rounded-2xl shadow-lg">
        <h1 class="text-2xl font-bold mb-6 text-white">
            Nowy szablon wsadu dla: <span class="text-rose-400">{{ $automat?->nazwa ?? '---' }}</span>
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

        <form action="{{ route('wsad-template.store') }}" method="POST" class="space-y-6">
            @csrf

            @if($automat)
                <input type="hidden" name="automat_id" value="{{ $automat->id }}">
            @endif

            <div>
                <label for="nazwa" class="block text-sm font-medium text-gray-300 mb-1">Nazwa szablonu (opcjonalna)</label>
                <input
                    type="text"
                    name="nazwa"
                    id="nazwa"
                    class="w-full px-3 py-2 rounded-lg border border-gray-600 bg-gray-700 text-white"
                    placeholder="Np. Wsad poranny A">
            </div>

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
                        
                        <button type="button" class="h-[42px] px-3 bg-red-800 hover:bg-red-600 text-white rounded-lg transition-colors remove-item flex items-center justify-center">
                            Usuń
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-4">
                <button type="button" id="dodaj-produkt" class="px-4 py-2 bg-green-800 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors flex items-center gap-1">
                    ✚ Dodaj produkt
                </button>
                
                <button
                    type="submit"
                    onclick="return confirm('Utworzyć szablon wsadu?')"
                    class="px-4 py-2 bg-blue-800 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors">
                    💾 Zapisz szablon
                </button>
                <a href="{{ route('wsad-template.show', ['automat' => $automat->id]) }}"
                class="px-4 py-2 bg-yellow-800 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-colors">
                Powrót
            </a>
            </div>
        </form>
    </div>

    <script>
        window._produkty = @json($produkty); // autouzupełnianie nazw, opcjonalne
    </script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/wsady-create.js') }}"></script>
</x-layout>
