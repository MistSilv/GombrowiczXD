<div class="bg-gray-900 rounded-xl shadow-xl p-4 sm:p-6 mb-8">
    <!-- Filtry -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Ilość na stanie -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
            <label class="block text-white font-semibold mb-2 text-sm">Ilość na stanie < niż:</label>
            <input id="maxStan" type="number" wire:keydown.debounce.300ms="$set('maxStan', $event.target.value)" min="0"
                   class="w-full px-4 py-2 rounded-lg bg-white/90 border-0 text-gray-800 focus:ring-2 focus:ring-blue-500"
                   placeholder="Np. 200">
        </div>

        <!-- Filtruj po nazwie -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
            <label class="block text-white font-semibold mb-2 text-sm">Filtruj po nazwie:</label>
            <input id="filterNazwa" type="text" wire:keydown.debounce.300ms="$set('filterNazwa', $event.target.value)"
                   class="w-full px-4 py-2 rounded-lg bg-white/90 border-0 text-gray-800 focus:ring-2 focus:ring-blue-500"
                   placeholder="Wpisz nazwę produktu">
        </div>

        <!-- Filtruj po EAN -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
            <label class="block text-white font-semibold mb-2 text-sm">Filtruj po kodzie EAN:</label>
            <input id="filterEan" type="text" wire:keydown.debounce.300ms="$set('filterEan', $event.target.value)"
                   class="w-full px-4 py-2 rounded-lg bg-white/90 border-0 text-gray-800 focus:ring-2 focus:ring-blue-500"
                   placeholder="Wpisz kod EAN">
        </div>

        <!-- Przełącznik Pokaż wszystko -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3 flex flex-col justify-end">
            <label class="block text-white font-semibold mb-2 text-sm">Pokaż wszystko:</label>
            <label class="relative inline-flex items-center cursor-pointer self-start">
                <input type="checkbox" id="showEmpty" wire:click="$toggle('showEmpty')" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:bg-purple-600 transition duration-300"></div>
                <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-300 peer-checked:translate-x-6"></div>
            </label>
        </div>
    </div>

    <!-- Tabela -->
    <div class="overflow-hidden rounded-xl shadow-md bg-white/90 backdrop-blur-sm">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Stan Produktów niekraftowych</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm sm:text-base">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-700 uppercase tracking-wider">Produkt</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700 uppercase tracking-wider">Wsady</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700 uppercase tracking-wider">Zam.</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700 uppercase tracking-wider">Na Stanie</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse($deficyty as $d)
                    @php
                        $wsady = $d->wsady->sum('pivot.ilosc');
                        $zamowienia = $d->zamowienia->sum('pivot.ilosc');
                        $naStanie = $zamowienia - $wsady;
                    @endphp
                    <tr 
                        class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer deficyt-row" 
                        data-produkt-id="{{ $d->id }}"
                        data-deficyt="{{ $naStanie }}"
                    >
                        <td class="px-4 py-3 text-blue-600 hover:text-blue-800">
                            <span class="font-medium product-name">{{ $d->tw_nazwa }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $wsady }}</td>
                        <td class="px-4 py-3">{{ $zamowienia }}</td>
                        <td class="px-4 py-3 font-medium {{ $naStanie < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $naStanie }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">Brak wyników spełniających kryteria</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginacja -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
            {{ $deficyty->links() }}
        </div>
    </div>
</div>