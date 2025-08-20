<x-layout>
    <div class="container mx-auto px-4 py-6 text-white max-w-3xl bg-gray-900/70 rounded-xl">
        <h1 class="text-3xl font-extrabold text-center text-purple-300 mb-8">
            Szczegóły wsadu #{{ $wsad->id }}
        </h1>

        <!-- Informacje o wsadzie -->
        <div class="bg-gray-900/70 rounded-xl shadow-lg p-5 sm:p-6 mb-6 space-y-3 border border-gray-700">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-400 font-semibold">Automat:</span>
                <span class="text-base font-medium">
                    {{ $wsad->automat->nazwa ?? 'Brak danych' }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-400 font-semibold">Data wsadu:</span>
                <span class="text-base font-medium">
                    {{ $wsad->data_wsadu ? $wsad->data_wsadu->format('d-m-Y H:i') : 'Brak danych' }}
                </span>
            </div>
        </div>

        <!-- Lista produktów -->
        <div class="bg-gray-900/70 rounded-xl shadow-lg p-5 sm:p-6 border border-gray-700">
            <h2 class="text-xl sm:text-2xl font-semibold text-purple-200 mb-4">
                Lista produktów
            </h2>

            @if($wsad->produkty->isEmpty())
                <p class="text-gray-400 italic">Brak produktów.</p>
            @else
                <div class="overflow-x-auto rounded-md">
                    <table class="min-w-full text-sm divide-y divide-gray-700 border border-gray-700">
                        <thead class="bg-gray-800 text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-2 text-left">Produkt</th>
                                <th class="px-4 py-2 text-left">Ilość</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($produkty as $produkt)
                                <tr class="hover:bg-gray-800/50 transition duration-200">
                                    <td class="px-4 py-2 text-white">{{ $produkt->tw_nazwa }}</td>
                                    <td class="px-4 py-2 text-white">{{ $produkt->pivot->ilosc }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-6 text-center">
                            {{ $produkty->links('pagination::simple-tailwind') }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Powrót jako przycisk -->
        <div class="mt-8 text-center">
            <a href="{{ route('wsady.index') }}"
               class="inline-block px-6 py-2 bg-rose-950 hover:bg-red-900 text-white font-semibold rounded-lg shadow transition duration-200">
                ← Powrót do listy wsadów
            </a>
        </div>
    </div>
</x-layout>
