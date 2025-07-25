<x-layout>
    <div class="container mx-auto px-4 py-6 text-white max-w-4xl bg-gray-900/70 rounded-xl">
        <h1 class="text-3xl font-extrabold text-center text-rose-300 mb-6">
            Podsumowanie strat ({{ $typ }})
        </h1>
        <p class="text-center text-gray-400 mb-8">Okres: <strong>{{ $okres }}</strong></p>

        @if ($produkty->isEmpty())
            <div class="bg-gray-900/60 rounded-xl shadow-md p-6 text-center text-gray-400">
                Brak strat w tym okresie.
            </div>
        @else
            @php
                $zakresMap = [
                    'Dzień' => 'dzien',
                    'Tydzień' => 'tydzien',
                    'Miesiąc' => 'miesiac',
                    'Rok' => 'rok',
                ];
                $zakresSlug = $zakresMap[$typ] ?? 'dzien';
                $dateForUrl = \Illuminate\Support\Str::before($okres, ' do');
            @endphp

            <!-- Eksport przyciski -->
            <div class="mb-6 flex flex-wrap justify-center gap-4">
                <a href="{{ route('export.straty', ['zakres' => $zakresSlug, 'format' => 'xlsx', 'date' => $dateForUrl]) }}"
                   class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    Eksportuj do Excel (.xlsx)
                </a>
                <a href="{{ route('export.straty', ['zakres' => $zakresSlug, 'format' => 'csv', 'date' => $dateForUrl]) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition">
                    Eksportuj do CSV
                </a>
            </div>

            <!-- Tabela strat -->
            <div class="bg-gray-900/60 rounded-xl shadow-md overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700 text-sm">
                    <thead class="bg-gray-800 text-purple-200">
                        <tr>
                            <th class="px-6 py-3 text-left uppercase tracking-wider font-semibold">Produkt</th>
                            <th class="px-6 py-3 text-left uppercase tracking-wider font-semibold">Łączna strata (szt.)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($produkty as $produkt)
                            <tr class="hover:bg-gray-800/70 transition">
                                <td class="px-6 py-3">{{ $produkt->tw_nazwa }}</td>
                                <td class="px-6 py-3 text-rose-400 font-semibold">{{ $produkt->suma }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Powrót -->
        <div class="mt-8 text-center">
            <a href="{{ route('straty.index') }}"
               class="inline-block px-6 py-2 bg-rose-950 hover:bg-red-900 text-white font-semibold rounded-lg shadow transition duration-200">
                ← Wróć do listy strat
            </a>
        </div>
    </div>
</x-layout>
