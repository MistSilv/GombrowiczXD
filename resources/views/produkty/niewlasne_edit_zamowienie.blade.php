<x-layout>
    <h1 class="text-xl sm:text-3xl font-bold mb-6 text-white text-center sm:text-left">
        Edytuj ilości produktów dla zamówienia
    </h1>

    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-8 mb-6">
        <div class="flex flex-col flex-1 mb-4 sm:mb-0">
            <label for="maxStan" class="text-white font-semibold mb-1">Pokaż tylko produkty z ilością na stanie mniejszą niż:</label>
            <input type="number" id="maxStan" min="0"
                class="px-3 py-2 rounded border border-gray-300 w-full sm:w-64 text-black focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Np. 200">
        </div>
        <div class="flex flex-col flex-1">
            <label for="filterNazwa" class="text-white font-semibold mb-1">Filtruj po nazwie produktu:</label>
            <input type="text" id="filterNazwa" 
                class="px-3 py-2 rounded border border-gray-300 w-full sm:w-64 text-black focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Wpisz nazwę produktu">
        </div>
    </div>

    {{-- Tabela deficytów --}}
    <div class="p-4 bg-white rounded shadow-md overflow-x-auto mb-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Deficyty Produktów Obcych</h2>
        <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm sm:text-base">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-3 py-2 text-left">Produkt</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Wsady</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Zamówienia</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Na Stanie</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deficyty as $d)
                    <tr class="deficyt-row hover:bg-gray-50 cursor-pointer" data-deficyt="{{ $d['na_stanie'] }}">
                        <td class="border border-gray-300 px-3 py-2 product-name text-blue-600 underline">
                            {{ $d['nazwa'] }}
                        </td>
                        <td class="border border-gray-300 px-3 py-2">{{ $d['wsady'] }}</td>
                        <td class="border border-gray-300 px-3 py-2">{{ $d['zamowienia'] }}</td>
                        <td class="border border-gray-300 px-3 py-2
                            {{ $d['na_stanie'] < 0 ? 'text-red-600 font-bold' : 'text-green-700' }}">
                            {{ $d['na_stanie'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Formularz zamówień --}}
    <form action="{{ route('produkty.zamowienie.zapisz') }}" method="POST" class="space-y-4" id="zamowienieForm">
        @csrf
        <input type="hidden" name="zamowienieId" value="{{ $zamowienieId ?? '' }}">
        <input type="hidden" name="wyslij_email" id="wyslijEmail" value="0">

        <div class="overflow-x-auto bg-white rounded shadow-md">
            <table class="min-w-full table-auto text-sm sm:text-base" id="produkty-lista">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-2 sm:px-6 text-left font-bold text-gray-700">Nazwa produktu</th>
                        <th class="py-3 px-2 sm:px-6 text-left font-bold text-gray-700 w-24">Ilość</th>
                        <th class="py-3 px-2 sm:px-6 w-12"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- start puste --}}
                </tbody>
            </table>
        </div>

        <button type="button" id="dodaj-produkt"
            class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition w-full sm:w-auto">
            + Dodaj produkt
        </button>

        <div
            class="mt-6 flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-4 max-w-xl mx-auto sm:mx-0">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded transition duration-300 w-full sm:w-auto"
                onclick="if(confirm('Czy na pewno chcesz zapisać i wysłać email?')) { document.getElementById('wyslijEmail').value = '1'; return true; } return false;">
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
