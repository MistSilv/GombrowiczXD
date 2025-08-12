<x-layout>
    <div class="max-w-4xl mx-auto p-6 bg-gray-900 rounded-2xl shadow-lg">
        <h1 class="text-2xl font-bold mb-6 text-white">
            Nowy szablon zamówienia
        </h1>

        <form action="{{ route('produkty.templates.store') }}" method="POST" class="space-y-6" id="zamowienieForm">
            @csrf
            <div class="flex items-center space-x-3">
                <label for="is_wlasny" class="text-white font-semibold">Zamówienie na produkty własne</label>
                <button type="button" id="toggleIsWlasny" aria-pressed="false" 
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    <span class="sr-only">Toggle zamówienie własne</span>
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-1" id="toggleKnob"></span>
                </button>
                <input type="hidden" name="is_wlasny" id="is_wlasny" value="0">
            </div>

            <!-- Pole nazwa szablonu -->
            <div>
                <label for="nazwa" class="text-white font-semibold mb-1 block">Nazwa szablonu</label>
                <input
                    type="text"
                    name="nazwa"
                    id="nazwa"
                    placeholder="Podaj nazwę szablonu"
                    class="w-full px-3 py-2 rounded-md shadow-sm border text-white"
                    required>
            </div>

            <!-- Skaner kodów -->
            <div class="mb-8 bg-gray-700 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-4 text-white">Skanuj kod EAN</h2>
                <div class="flex flex-col sm:flex-row gap-4 items-start">
                    <button id="start-scan" type="button" class="px-4 py-2 rounded-lg bg-blue-800 hover:bg-blue-600 text-white font-semibold transition-colors">
                        Rozpocznij skanowanie
                    </button>
                    <div id="reader" class="w-full sm:w-64 mx-auto sm:mx-0 hidden"></div>
                </div>
                <div id="scan-result" class="mt-3 text-green-400 font-medium"></div>
            </div>

            <!-- Tabela produktów -->
            <div class="overflow-hidden rounded-md shadow">
                <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm" id="produkty-lista">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-2 text-left font-bold text-gray-700 uppercase">Nazwa produktu</th>
                            <th class="py-2 px-2 text-right font-bold text-gray-700 uppercase w-20">Ilość</th>
                            <th class="py-2 px-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- start pusty -->
                    </tbody>
                </table>
            </div>

            <!-- Wyszukiwanie produktów -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <!-- Nazwa -->
                <div>
                    <label for="product-search" class="text-white font-semibold mb-1 mt-2 block">
                        <i class="fas fa-search mr-1"></i>Produkt
                    </label>
                    <input
                        type="text"
                        id="product-search"
                        placeholder="Nazwa produktu..."
                        class="w-full px-3 py-2 rounded-md shadow-sm border text-white"
                        autocomplete="off">
                    <ul id="product-suggestions" class="absolute z-50 mt-1 w-full bg-white shadow rounded-md max-h-48 overflow-y-auto hidden border border-gray-200"></ul>
                </div>

                <!-- EAN -->
                <div>
                    <label for="product-search-ean" class="text-white font-semibold mb-1 mt-2 block">
                        <i class="fas fa-barcode mr-1"></i>EAN/PLU
                    </label>
                    <div class="flex gap-2">
                        <input
                            type="number"
                            id="product-search-ean"
                            placeholder="EAN/PLU..."
                            class="flex-1 px-3 py-2 rounded-md shadow-sm border text-white"
                            autocomplete="off"
                            inputmode="numeric"
                            max="9999999999999"
                            oninput="this.value=this.value.slice(0,13)">
                        <button type="button" id="dodaj-ean" class="px-3 py-2 bg-green-800 hover:bg-green-600 text-white font-semibold rounded-md text-sm">+</button>
                    </div>
                </div>
            </div>

            <!-- Przyciski -->
            <div class="flex flex-wrap gap-4 pt-4">
                <button
                    type="submit"
                    onclick="return confirm('Czy na pewno chcesz utworzyć szablon zamówienia?')"
                    class="px-4 py-2 bg-blue-800 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors">
                    💾 Zapisz szablon
                </button>
                <a href="{{ route('produkty.templates.index') }}"
                   class="px-4 py-2 bg-yellow-800 hover:bg-yellow-600 text-white font-semibold rounded-lg transition-colors">
                   Powrót
                </a>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="application/json" id="produkty-data">@json($produkty)</script>
>
    <script src="{{ asset('js/zamowienia-template.js') }}"></script>
</x-layout>
