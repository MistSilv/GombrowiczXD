<x-layout>
    <h1 class="text-xl sm:text-3xl font-bold mb-6 text-white">Edytuj ilości produktów dla zamówienia</h1>

    @if(session('success'))
        <div class="bg-green-200 text-green-800 px-4 py-2 rounded mb-4 text-sm sm:text-base">
            {{ session('success') }}
        </div>
    @endif

    @if(session('email_sent'))
        <div class="bg-blue-200 text-blue-800 px-4 py-2 rounded mb-4 text-sm sm:text-base">
            {{ session('email_sent') }}
        </div>
    @endif

    <div class="mb-4">
        <label for="minDeficyt" class="block text-white font-semibold mb-1">Pokaż tylko deficyty większe lub równe:</label>
        <input type="number" id="minDeficyt" value="500" min="0"
            class="px-3 py-2 rounded border w-full sm:w-64 text-black"
            placeholder="Np. 500">
    </div>
    
    {{-- Tabela deficytów --}}
    <div class="p-4">
        <h2 class="text-xl font-bold mb-4">Deficyty Produktów Obcych</h2>
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="border px-4 py-2">Produkt</th>
                    <th class="border px-4 py-2">Wsady</th>
                    <th class="border px-4 py-2">Zamówienia</th>
                    <th class="border px-4 py-2">Na Stanie</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deficyty as $d)
                    <tr class="deficyt-row cursor-pointer" data-produkt-id="{{ $d['id'] }}">
                        <td class="border px-4 py-2">{{ $d['nazwa'] }}</td>
                        <td class="border px-4 py-2">{{ $d['wsady'] }}</td>
                        <td class="border px-4 py-2">{{ $d['zamowienia'] }}</td>
                        <td class="border px-4 py-2 {{ $d['na_stanie'] < 0 ? 'text-red-600 font-bold' : 'text-green-700' }}">
                            {{ $d['na_stanie'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Formularz --}}
    <form action="{{ route('produkty.zamowienie.zapisz') }}" method="POST" class="space-y-4" id="zamowienieForm">
        @csrf
        <input type="hidden" name="zamowienieId" value="{{ $zamowienieId ?? '' }}">
        <input type="hidden" name="wyslij_email" id="wyslijEmail" value="0">

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded shadow text-sm sm:text-base" id="produkty-lista">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-2 sm:px-6 text-left font-bold text-gray-700">Nazwa produktu</th>
                        <th class="py-3 px-2 sm:px-6 text-left font-bold text-gray-700">Ilość</th>
                        <th class="py-3 px-2 sm:px-6"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Pusta na start --}}
                </tbody>
            </table>
        </div>

        <button type="button" id="dodaj-produkt"
            class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
            + Dodaj produkt
        </button>

        <div class="mt-6 flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-4">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded transition duration-300 w-full sm:w-auto"
                onclick="if(confirm('Czy na pewno chcesz zapisać i wysłać email?')) { document.getElementById('wyslijEmail').value = '1'; return true; } return false;">
                <i class="fas fa-paper-plane mr-2"></i>Zapisz i wyślij email
            </button>

            <a href="{{ url('/welcome') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300 w-full sm:w-auto">
                <i class="fas fa-arrow-left mr-2"></i>Powrót
            </a>
        </div>
    </form>

    <script>
        window._produkty = @json($produkty); // Laravel blade
    </script>
    <script src="{{ asset('js/niewlasne-create.js') }}"></script>
</x-layout>
