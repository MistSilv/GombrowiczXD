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

    {{-- Tabela deficytów --}}
    <div class="overflow-x-auto mb-6">
        <table class="min-w-full bg-white rounded shadow text-sm sm:text-base">
            <thead class="bg-gray-300">
                <tr>
                    <th class="py-2 px-2 sm:px-4 text-left">Produkt</th>
                    <th class="py-2 px-2 sm:px-4 text-left">Wsady</th>
                    <th class="py-2 px-2 sm:px-4 text-left">Zamówienia</th>
                    <th class="py-2 px-2 sm:px-4 text-left">Różnica</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($deficyty as $item)
                    <tr
                        @class([
                            'cursor-pointer hover:bg-green-100 font-semibold' => $item['deficyt'] > 0,
                            'bg-red-100 text-red-700 font-semibold cursor-not-allowed' => $item['deficyt'] < 0,
                            'bg-white' => $item['deficyt'] == 0,
                        ])
                        @if($item['deficyt'] > 0)
                            onclick="setQuantity({{ $item['id'] }}, {{ $item['deficyt'] }})"
                            title="Kliknij, aby wstawić {{ $item['deficyt'] }} do formularza"
                        @endif
                    >
                        <td class="py-2 px-2 sm:px-4 break-words max-w-[150px]">{{ $item['nazwa'] }}</td>
                        <td class="py-2 px-2 sm:px-4">{{ $item['wsady'] }}</td>
                        <td class="py-2 px-2 sm:px-4">{{ $item['zamowienia'] }}</td>
                        <td class="py-2 px-2 sm:px-4">{{ $item['deficyt'] }}</td>
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
            <table class="min-w-full bg-white rounded shadow text-sm sm:text-base">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-2 sm:px-6 text-left font-bold text-gray-700">Nazwa produktu</th>
                        <th class="py-3 px-2 sm:px-6 text-left font-bold text-gray-700">Ilość</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produkty as $produkt)
                        <tr class="border-b last:border-0 hover:bg-gray-100" data-produkt-id="{{ $produkt->id }}">
                            <td class="py-3 px-2 sm:px-6 break-words max-w-[200px]">{{ $produkt->tw_nazwa }}</td>
                            <td class="py-3 px-2 sm:px-6">
                                <input
                                    type="number"
                                    name="ilosci[{{ $produkt->id }}]"
                                    step="1"
                                    min="0"
                                    max="3000"
                                    value="{{ $produkt->ilosc ?? 0 }}"
                                    class="border rounded px-2 py-1 w-full max-w-[6rem] sm:w-32"
                                    id="ilosc-{{ $produkt->id }}"
                                >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Przyciski --}}
        <div class="mt-6 flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-4">
            <button 
                type="submit" 
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded transition duration-300 w-full sm:w-auto"
                onclick="document.getElementById('wyslijEmail').value = '1'"
            >
                <i class="fas fa-paper-plane mr-2"></i>Zapisz i wyślij email
            </button>

            <a href="{{ url('/welcome') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300 w-full sm:w-auto">
                <i class="fas fa-arrow-left mr-2"></i>Powrót
            </a>
        </div>
    </form>

    <script>
        function setQuantity(produktId, ilosc) {
            const input = document.getElementById('ilosc-' + produktId);
            if(input) {
                input.value = ilosc;
                input.focus();
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    </script>
</x-layout>
