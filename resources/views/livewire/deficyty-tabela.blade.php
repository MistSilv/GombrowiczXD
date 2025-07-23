<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:gap-8 mb-6">
        <div class="flex flex-col flex-1 mb-4 sm:mb-0">
            <label class="text-white font-semibold mb-1">Ilość na stanie < niż:</label>
            <input id="maxStan" type="number" wire:keydown.debounce.300ms="$set('maxStan', $event.target.value)" min="0"
                   class="px-3 py-2 mb-4 rounded border border-gray-300 w-full sm:w-64 text-black focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Np. 200">
        </div>
        <div class="flex flex-col flex-1">
            <label class="text-white font-semibold mb-1">Filtruj po nazwie produktu:</label>
            <input id="filterNazwa" type="text" wire:keydown.debounce.300ms="$set('filterNazwa', $event.target.value)"
                   class="px-3 py-2 mb-4 rounded border border-gray-300 w-full sm:w-64 text-black focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Wpisz nazwę produktu">
        </div>
        <div class="flex flex-col flex-1">
            <label class="text-white font-semibold mb-1">Filtruj po kodzie EAN:</label>
            <input id="filterEan" type="text" wire:keydown.debounce.300ms="$set('filterEan', $event.target.value)"
                class="px-3 py-2 mb-4 rounded border border-gray-300 w-full sm:w-64 text-black focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Wpisz kod EAN">
        </div>

        <div class="flex flex-col flex-1">
            <label for="showEmpty" class="text-white font-semibold mb-1">Pokaż wszystko</label>
            
            <label class="relative inline-flex items-center cursor-pointer mb-4">
                <input type="checkbox" id="showEmpty" wire:click="$toggle('showEmpty')"  class="sr-only peer">
                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:bg-purple-600 transition duration-300"></div>
                <div class="absolute left-0.5 top-0.5 bg-white w-5 h-5 rounded-full transition-transform duration-300 peer-checked:translate-x-full"></div>
            </label>
        </div>

    </div>

    <div class="p-4 bg-white rounded shadow-md overflow-x-auto mb-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Stan Produktów niekraftowych</h2>
        <table class="min-w-full table-auto border-collapse border border-gray-300 text-sm sm:text-base">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-3 py-2 text-left">Produkt</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Wsady</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Zam.</th>
                    <th class="border border-gray-300 px-3 py-2 text-left">Na Stanie</th>
                </tr>
            </thead>
            <tbody>
            @forelse($deficyty as $d)
                @php
                    $wsady = $d->wsady->sum('pivot.ilosc');
                    $zamowienia = $d->zamowienia->sum('pivot.ilosc');
                    $naStanie = $zamowienia - $wsady;
                @endphp
                <tr 
                    class="hover:bg-gray-50 cursor-pointer deficyt-row" 
                    data-produkt-id="{{ $d->id }}"
                    data-deficyt="{{ $naStanie }}"
                >
                    <td class="border border-gray-300 px-3 py-2 text-blue-600 underline product-name">
                        {{ $d->tw_nazwa }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2">{{ $wsady }}</td>
                    <td class="border border-gray-300 px-3 py-2">{{ $zamowienia }}</td>
                    <td class="border border-gray-300 px-3 py-2 {{ $naStanie < 0 ? 'text-red-600 font-bold' : 'text-green-700' }}">
                        {{ $naStanie }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-3 text-center text-gray-500">Brak wyników</td>
                </tr>
            @endforelse
        </tbody>

        </table>
        <div class="mt-6">
            {{ $deficyty->links() }}
        </div>
    </div>
   

</div>
