<x-layout>
    <div class="container mx-auto px-4 py-6 text-white max-w-4xl bg-gray-900/70 rounded-xl">
        <h1 class="text-3xl font-extrabold text-center text-purple-300 mb-2">
            Podsumowanie zamówień ({{ $typ }})
        </h1>
        <p class="text-center text-sm text-gray-400 mb-6">
            Okres: <strong class="text-white">{{ $okres }}</strong>
        </p>

        @if($produkty->isEmpty())
            <p class="text-center text-gray-400 italic">Brak zamówień w tym okresie.</p>
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

            <!-- Przyciski eksportu -->
            <div class="mb-6 flex flex-wrap justify-center gap-4">
                <a href="{{ route('export.unified', [
                        'typ' => 'zamowienia',
                        'zakres' => $zakresSlug,
                        'od' => $dateForUrl,
                        'format' => 'xlsx',
                    ]) }}"
                class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-5 py-2 rounded-lg shadow transition duration-200">
                    📊 Eksportuj do Excel (.xlsx)
                </a>

                <a href="{{ route('export.unified', [
                        'typ' => 'zamowienia',
                        'zakres' => $zakresSlug,
                        'od' => $dateForUrl,
                        'format' => 'csv',
                    ]) }}"
                class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-2 rounded-lg shadow transition duration-200">
                    📄 Eksportuj do CSV
                </a>
            </div>

            <!-- Tabela z produktami -->
            <div class="overflow-x-auto bg-gray-900/50 border border-gray-700 rounded-lg shadow">
                <table class="min-w-full text-sm text-white divide-y divide-gray-700">
                    <thead class="bg-gray-800 text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Produkt</th>
                            <th class="px-4 py-3 text-left">Łączna ilość</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($produkty as $produkt)
                            <tr class="hover:bg-gray-800/60 transition duration-150">
                                <td class="px-4 py-2">{{ $produkt->tw_nazwa }}</td>
                                <td class="px-4 py-2 font-semibold text-purple-400">{{ $produkt->suma }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Powrót jako przycisk -->
        <div class="mt-8 text-center">
            <a href="{{ route('zamowienia.index', ['automat_id' => request('automat_id')]) }}"
               class="inline-block px-6 py-2 bg-rose-950 hover:bg-red-900 text-white font-semibold rounded-lg shadow transition duration-200">
                ← Powrót do listy zamówień
            </a>
        </div>
    </div>
</x-layout>
