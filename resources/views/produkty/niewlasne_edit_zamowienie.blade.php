<x-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Sekcja deficytów -->
            @livewire('deficyty-tabela')

            <!-- Formularz zamówień -->
        <div class="bg-gray-900 rounded-lg shadow-md p-3 sm:p-4 mb-6 text-sm">
            <form action="{{ route('produkty.zamowienie.zapisz') }}" method="POST" class="space-y-4" id="zamowienieForm">
                @csrf
                <input type="hidden" name="zamowienieId" value="{{ $zamowienieId ?? '' }}">
                <input type="hidden" name="wyslij_email" id="wyslijEmail" value="0">

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
                            <!-- start puste -->
                        </tbody>
                    </table>
                </div>

                <!-- Wyszukiwarka produktów -->
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
                                maxlength="13">
                            <button type="button" id="dodaj-ean" class="px-3 py-2 bg-rose-950 hover:bg-red-900 text-white font-semibold rounded-md text-sm">+</button>
                        </div>
                    </div>
                </div>

                <!-- Skaner kodów -->
                <div class="bg-white/10 backdrop-blur-sm rounded-md p-4 mt-4 text-center max-w-md mx-auto">
                    <h2 class="text-white font-bold text-base mb-3">
                        <i class="fas fa-qrcode mr-2"></i>Skanowanie kodów EAN
                    </h2>
                    <button 
                        type="button" 
                        id="start-scan" 
                        class="px-4 py-2 bg-rose-950 hover:bg-red-900 text-white font-semibold rounded-md text-sm">
                        <i class="fas fa-camera mr-1"></i> Rozpocznij
                    </button>
                    <div id="reader" class="mt-3 hidden"></div>
                    <div id="scan-result" class="mt-2 text-white text-sm"></div>
                </div>

                <!-- Przyciski -->
                <div class="flex flex-col sm:flex-row justify-center gap-3 pt-4">
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-rose-950 hover:bg-red-900 text-white font-bold rounded-md w-44 text-sm text-center mx-auto sm:mx-0"
                        onclick="return confirm('Czy na pewno chcesz dodać zamówienie?')">
                        Zapisz i wyślij
                    </button>
                    <a 
                        href="{{ url('/welcome') }}"
                        class="px-4 py-2 bg-rose-950 hover:bg-red-900 text-white font-bold rounded-md w-44 text-sm text-center mx-auto sm:mx-0">
                        Powrót
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="application/json" id="produkty-data">@json($produkty)</script>
    <script src="{{ asset('js/niewlasne.js') }}"></script>
</x-layout>