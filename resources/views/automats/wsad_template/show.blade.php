<x-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 bg-gray-900 rounded-xl sm:rounded-2xl shadow-lg">
        <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">
            Automat: <span class="text-rose-400">{{ $automat->nazwa }}</span>
        </h1>
        <p class="text-gray-300 mb-4 sm:mb-6">Lokalizacja: {{ $automat->lokalizacja }}</p>

        @if($automat->wsadTemplates->count())
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-white mb-3 sm:mb-4">Wybierz szablon do modyfikacji</h2>
                <ul class="mb-4 sm:mb-6 space-y-2">
                    @foreach($automat->wsadTemplates as $template)
                        <li>
                            <button 
                                type="button" 
                                class="template-select-btn text-left w-full px-3 py-2 sm:px-4 sm:py-2 rounded hover:bg-gray-700 transition text-sm sm:text-base
                                    {{ $template->is_active ? 'bg-green-950 text-green-200 font-bold' : 'bg-gray-800 text-white' }}" 
                                data-template-id="{{ $template->id }}">
                                @if($template->is_active)
                                    <span class="mr-1">✅</span>
                                @endif
                                {{ $template->nazwa }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div>
                    @foreach($automat->wsadTemplates as $template)
                        <div class="template-details p-3 sm:p-4 bg-gray-800 rounded-lg sm:rounded-xl border border-gray-700 mb-4 sm:mb-6" 
                             data-template-id="{{ $template->id }}" style="display:none;">
                             
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                <div class="flex-grow">
                                    <input 
                                        type="text" 
                                        name="nazwa" 
                                        value="{{ $template->nazwa }}" 
                                        class="w-full bg-gray-700 text-white px-2 py-1 sm:px-3 sm:py-1 rounded border border-gray-600 focus:outline-none focus:ring text-sm sm:text-base"
                                        disabled>
                                </div>

                                <div class="flex flex-wrap gap-2 justify-end">
                                    @if(!$template->is_active)
                                        <form method="POST" action="{{ route('wsad-template.activate', $template->id) }}" class="flex-shrink-0">
                                            @csrf
                                            <button type="submit" class="bg-green-800 hover:bg-green-600 text-white px-2 py-1 sm:px-3 sm:py-1 rounded text-xs sm:text-sm whitespace-nowrap">
                                                ✅ Aktywny
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('wsad-template.deactivate', $template->id) }}" class="flex-shrink-0">
                                            @csrf
                                            <button type="submit" class="bg-amber-800 hover:bg-yellow-600 text-white px-2 py-1 sm:px-3 sm:py-1 rounded text-xs sm:text-sm whitespace-nowrap">
                                                🩻 Dezaktywuj
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('wsad-template.destroy', $template->id) }}" class="flex-shrink-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Na pewno chcesz usunąć ten szablon?')"
                                            class="bg-red-800 hover:bg-red-600 text-white px-2 py-1 sm:px-3 sm:py-1 rounded text-xs sm:text-sm whitespace-nowrap">
                                            🗑 Usuń
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-3">
                                <button type="button" class="edit-btn bg-blue-700 hover:bg-blue-600 text-white px-2 py-1 sm:px-3 sm:py-1 rounded text-xs sm:text-sm whitespace-nowrap">
                                    ✏️ Modyfikuj
                                </button>
                                <button type="button" class="save-btn bg-green-800 hover:bg-green-600 text-white px-2 py-1 sm:px-3 sm:py-1 rounded text-xs sm:text-sm whitespace-nowrap hidden">
                                    💾 Zapisz
                                </button>
                                <button type="button" class="dodaj-produkt bg-green-800 hover:bg-green-600 text-white px-2 py-1 sm:px-3 sm:py-1 rounded text-xs sm:text-sm whitespace-nowrap hidden">
                                    ✚ Produkt
                                </button>
                            </div>

                            <div>
                                <table class="w-full table-auto text-left text-white border-t border-gray-600 text-sm sm:text-base">
                                    <thead class="text-xs sm:text-sm text-gray-300">
                                        <tr>
                                            <th class="px-1 py-1 sm:px-2 sm:py-1">Produkt</th>
                                            <th class="px-1 py-1 sm:px-2 sm:py-1 w-16 sm:w-20">Ilość</th>
                                            <th class="px-1 py-1 sm:px-2 sm:py-1 w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="products-table">
                                        @foreach($template->produkty as $p)
                                            <tr class="border-t border-gray-700">
                                                <td class="px-1 py-1 sm:px-2 sm:py-1">
                                                    <input type="text" 
                                                           class="product-name-input bg-gray-700 text-white px-1 py-1 sm:px-2 sm:py-1 rounded w-full text-sm sm:text-base" 
                                                           value="{{ $p->produkt->tw_nazwa ?? '???' }}" 
                                                           data-produkt-id="{{ $p->produkt_id }}" 
                                                           disabled>
                                                </td>
                                                <td class="px-1 py-1 sm:px-2 sm:py-1">
                                                    <input type="number" min="0" 
                                                           value="{{ $p->ilosc }}" 
                                                           class="product-amount-input bg-gray-700 text-white px-1 py-1 sm:px-2 sm:py-1 rounded border border-gray-600 w-12 sm:w-16 text-sm sm:text-base" 
                                                           data-produkt-id="{{ $p->produkt_id }}" disabled>
                                                </td>
                                                <td class="px-1 py-1 sm:px-2 sm:py-1 text-right">
                                                    <button type="button" class="remove-product-btn text-red-800 hover:text-red-600 text-xs sm:text-sm hidden">
                                                        🗑️
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="mb-4 sm:mb-6">
            <a href="{{ route('wsad-template.create', ['automat' => $automat->id]) }}"
                class="inline-block bg-green-800 hover:bg-green-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg font-medium sm:font-semibold text-sm sm:text-base transition">
                ✚ Dodaj szablon
            </a>
            <a href="{{ route('wsad-template.index') }}" 
                class="inline-block ml-3 bg-yellow-800 hover:bg-yellow-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg font-medium sm:font-semibold text-sm sm:text-base transition">
                Powrót
            </a>
        </div>
    </div>

    <script>window._produkty = @json($produkty); </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/wsad-template.js') }}"></script>
    
    
    <style>
        .autocomplete-items {
            position: absolute;
            max-height: 150px;
            overflow-y: auto;
            z-index: 1000;
        }
    </style>
</x-layout>