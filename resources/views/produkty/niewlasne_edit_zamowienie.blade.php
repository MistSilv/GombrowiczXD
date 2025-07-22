<x-layout>
    
    @livewire('deficyty-tabela')

    {{-- Formularz zamówień --}}
    <div class="flex flex-col flex-1">
            <label for="WyslijMail" class="text-white font-semibold mb-1">Pobór z sklepu</label>
            
            <label class="relative inline-flex items-center cursor-pointer mb-4">
                <input type="checkbox" id="WyslijMail"   class="sr-only peer">
                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:bg-purple-600 transition duration-300"></div>
                <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition-transform duration-300 peer-checked:translate-x-full"></div>
            </label>
    </div>
    <form action="{{ route('produkty.zamowienie.zapisz') }}" method="POST" class="space-y-4" id="zamowienieForm">
        @csrf
        <input type="hidden" name="zamowienieId" value="{{ $zamowienieId ?? '' }}">
        <input type="hidden" name="wyslij_email" id="wyslijEmail" value="0">

        <div class="overflow-x-auto bg-white rounded shadow-md">
            <table class="min-w-full table-auto text-sm sm:text-base" id="produkty-lista">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-2 text-left font-bold text-gray-700 w-1/2 sm:w-auto">Nazwa</th>
                        <th class="py-3 px-2 text-right font-bold text-gray-700 w-1/3 sm:w-32">Ilość</th>
                        <th class="py-3 px-2 w-1/6 sm:w-12"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- start puste --}}
                </tbody>
            </table>
        </div>

        {{-- Wyszukiwarka produktów do dodania --}}
        <div class="mt-4">
            <label for="product-search" class="text-black font-semibold mb-1 block">Wyszukaj produkt do dodania:</label>
            <input type="text" id="product-search" placeholder="Wpisz nazwę produktu..."
                class="px-3 py-2 rounded border border-gray-300 w-full sm:w-96 text-black focus:outline-none focus:ring-2 focus:ring-blue-500" autocomplete="off">
            <ul id="product-suggestions" class="bg-white border border-gray-300 rounded max-h-48 overflow-y-auto mt-1 hidden z-50 absolute w-96"></ul>
        </div>

        <button type="button" id="dodaj-produkt"
            class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition w-full sm:w-auto">
            + Dodaj produkt
        </button>

        <div
            class="mt-6 flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-4 max-w-xl mx-auto sm:mx-0">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded transition duration-300 w-full sm:w-auto"
                onclick="return handleEmailSend();">
                <i class="fas fa-paper-plane mr-2"></i>Zapisz i wyślij email
            </button>

            <a href="{{ url('/welcome') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300 w-full sm:w-auto text-center">
                <i class="fas fa-arrow-left mr-2"></i>Powrót
            </a>
        </div>
    </form>

    <script type="application/json" id="produkty-data">@json($produkty)</script>
    <script src="{{ asset('js/niewlasne.js') }}"></script>
</x-layout>
