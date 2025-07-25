<x-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Sekcja deficytów -->
            @livewire('deficyty-tabela')


        <!-- Formularz zamówień -->
        <div class="bg-gray-900 rounded-lg shadow-xl p-4 sm:p-6 mb-8">
            <!-- Przełącznik poboru ze sklepu -->
            <div class="flex items-center justify-between mb-6 bg-white/10 backdrop-blur-sm rounded-lg p-3 sm:p-4">
                <label for="WyslijMail" class="text-white font-semibold text-sm sm:text-lg">Pobór ze sklepu</label>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="WyslijMail" class="sr-only peer">
                    <div class="w-12 h-6 sm:w-14 sm:h-7 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:bg-purple-600 transition duration-300"></div>
                    <div class="absolute left-1 top-1 bg-white w-4 h-4 sm:w-5 sm:h-5 rounded-full transition-transform duration-300 peer-checked:translate-x-6 sm:peer-checked:translate-x-7"></div>
                </label>
            </div>

            <form action="{{ route('produkty.zamowienie.zapisz') }}" method="POST" class="space-y-6" id="zamowienieForm">
                @csrf
                <input type="hidden" name="zamowienieId" value="{{ $zamowienieId ?? '' }}">
                <input type="hidden" name="wyslij_email" id="wyslijEmail" value="0">

                <!-- Tabela produktów -->
                <div class="overflow-hidden rounded-lg shadow-md">
                    <table class="min-w-full divide-y divide-gray-200" id="produkty-lista">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-3 sm:py-4 sm:px-4 text-left font-bold text-gray-700 uppercase tracking-wider text-sm sm:text-base">Nazwa produktu</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-4 text-right font-bold text-gray-700 uppercase tracking-wider w-24 sm:w-32 text-sm sm:text-base">Ilość</th>
                                <th class="py-3 px-3 sm:py-4 sm:px-4 w-10 sm:w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- start puste -->
                        </tbody>
                    </table>
                </div>

                <!-- Wyszukiwarka produktów -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Wyszukiwanie po nazwie -->
                    <div class="relative">
                        <label for="product-search" class="block text-white font-semibold mb-2 text-sm sm:text-base">
                            <i class="fas fa-search mr-2"></i>Wyszukaj produkt
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="product-search"
                                placeholder="Wpisz nazwę produktu..."
                                class="w-full px-3 py-2 sm:px-4 sm:py-3 rounded-lg border-0 shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-800 text-sm sm:text-base"
                                autocomplete="off">
                            <ul id="product-suggestions" class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-md max-h-60 overflow-y-auto hidden border border-gray-200"></ul>
                        </div>
                    </div>

                    <!-- Wyszukiwanie po EAN -->
                    <div class="relative">
                        <label for="product-search-ean" class="block text-white font-semibold mb-2 text-sm sm:text-base">
                            <i class="fas fa-barcode mr-2"></i>Wyszukaj po kodzie EAN/PLU
                        </label>
                        <div class="flex gap-2">
                            <input
                                type="number"
                                id="product-search-ean"
                                placeholder="Wpisz EAN/PLU..."
                                class="flex-1 px-3 py-2 sm:px-4 sm:py-3 rounded-lg border-0 shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent text-gray-800 text-sm sm:text-base"
                                autocomplete="off"
                                maxlength="13">
                            <button
                                type="button"
                                id="dodaj-ean"
                                class="px-3 py-2 sm:px-4 sm:py-3 bg-purple-700 hover:bg-purple-800 text-white font-semibold rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                                <i class="fas fa-plus sm:mr-2"></i> <span class="hidden sm:inline">Dodaj</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Skaner kodów -->
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 sm:p-6">
                    <h2 class="text-white font-bold text-lg sm:text-xl mb-3 sm:mb-4 flex items-center">
                        <i class="fas fa-qrcode mr-2 sm:mr-3"></i> Skanowanie kodów EAN
                    </h2>
                    
                    <div class="flex flex-col items-center">
                        <button 
                            type="button" 
                            id="start-scan" 
                            class="px-4 py-2 sm:px-6 sm:py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200 mb-3 sm:mb-4 flex items-center text-sm sm:text-base">
                            <i class="fas fa-camera mr-2"></i> Rozpocznij skanowanie
                        </button>

                        <div id="reader" class="w-full max-w-xs hidden"></div>
                        <div id="scan-result" class="mt-3 sm:mt-4 text-white text-center text-sm sm:text-base"></div>
                    </div>
                </div>

                <!-- Przyciski akcji -->
                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4 pt-4">
                    <button 
                        type="submit"
                        class="px-4 py-2 sm:px-8 sm:py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition duration-300 flex-1 sm:flex-none flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-paper-plane mr-2 sm:mr-3"></i> Zapisz i wyślij
                    </button>

                    <a 
                        href="{{ url('/welcome') }}"
                        class="px-4 py-2 sm:px-8 sm:py-3 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-lg transition duration-300 flex-1 sm:flex-none flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-arrow-left mr-2 sm:mr-3"></i> Powrót
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