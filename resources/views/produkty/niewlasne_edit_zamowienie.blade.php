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
                @foreach (collect($deficyty)->sortByDesc('deficyt') as $item)
                    @php
                        $deficyt = $item['deficyt'];
                        $rowClasses = 'deficyt-row';
                        $warningIcon = '';

                        if ($deficyt < 0) {
                            $rowClasses .= ' bg-purple-100 text-fuchsia-600 font-semibold cursor-not-allowed';
                        } elseif ($deficyt > 3000) {
                            $rowClasses .= ' bg-red-600 text-white font-bold cursor-pointer';
                            $warningIcon = '⚠️ ';
                        } elseif ($deficyt > 1000) {
                            $rowClasses .= ' bg-red-300 font-semibold cursor-pointer';
                        } elseif ($deficyt > 500) {
                            $rowClasses .= ' bg-yellow-100 font-semibold cursor-pointer';
                        } elseif ($deficyt > 200) {
                            $rowClasses .= ' bg-green-100 font-semibold cursor-pointer';
                        } elseif ($deficyt > 0) {
                            $rowClasses .= ' bg-white font-semibold cursor-pointer';
                        } else {
                            $rowClasses .= ' bg-white';
                        }
                    @endphp
                    <tr
                        class="{{ $rowClasses }}"
                        data-deficyt="{{ $deficyt }}"
                        @if($deficyt > 0)
                            onclick="setQuantity({{ $item['id'] }}, {{ $deficyt }})"
                            title="Kliknij, aby wstawić {{ $deficyt }} do formularza"
                        @endif
                    >
                        <td class="py-2 px-2 sm:px-4 break-words max-w-[150px]">
                            {!! $warningIcon !!}{{ $item['nazwa'] }}
                        </td>
                        <td class="py-2 px-2 sm:px-4">{{ $item['wsady'] }}</td>
                        <td class="py-2 px-2 sm:px-4">{{ $item['zamowienia'] }}</td>
                        <td class="py-2 px-2 sm:px-4">{{ $deficyt }}</td>
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
                    @foreach($produkty as $produkt)
                        <tr data-produkt-id="{{ $produkt->id }}">
                            <td class="py-3 px-2 sm:px-6 w-full">
                                <span>{{ $produkt->tw_nazwa }}</span>
                                <input type="hidden" name="ilosci[{{ $produkt->id }}]" value="{{ $produkt->ilosc ?? 0 }}">
                            </td>
                            <td class="py-3 px-2 sm:px-6">
                                <input
                                    type="number"
                                    min="0"
                                    max="3000"
                                    step="1"
                                    name="ilosci[{{ $produkt->id }}]"
                                    value="{{ $produkt->ilosc ?? 0 }}"
                                    class="border rounded px-2 py-1 w-full max-w-[6rem] sm:w-32 text-black"
                                    required
                                >
                            </td>
                            <td class="py-3 px-2 sm:px-6 text-right">
                                <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded transition remove-row">✕</button>
                            </td>
                        </tr>
                    @endforeach
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
