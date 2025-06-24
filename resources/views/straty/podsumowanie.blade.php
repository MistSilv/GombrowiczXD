<x-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4 text-white">Podsumowanie strat ({{ $typ }})</h1>
        <p class="mb-6">Okres: <strong>{{ $okres }}</strong></p>

        @if($produkty->isEmpty())
            <p>Brak strat w tym okresie.</p>
        @else
            @php
                $zakresMap = [
                    'Dzień' => 'dzien',
                    'Tydzień' => 'tydzien',
                    'Miesiąc' => 'miesiac',
                    'Rok' => 'rok',
                ];
                $zakresSlug = $zakresMap[$typ] ?? 'dzien';
                $dateForUrl = \Illuminate\Support\Str::before($okres, ' do'); // np. "2025-06-01"
            @endphp

            

            <div class="mb-4 flex gap-3">
                <a href="{{ route('export.straty', ['zakres' => $zakresSlug, 'format' => 'xlsx', 'date' => $dateForUrl]) }}"
                class="bg-green-500 hover:bg-green-800 text-white font-semibold py-2 px-4 rounded">
                    Eksportuj do Excel (.xlsx)
                </a>
                <a href="{{ route('export.straty', ['zakres' => $zakresSlug, 'format' => 'csv', 'date' => $dateForUrl]) }}"
                class="bg-blue-500 hover:bg-blue-800 text-white font-semibold py-2 px-4 rounded">
                    Eksportuj do CSV
                </a>
            </div>

            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">Produkt</th>
                        <th class="py-2 px-4 border-b">Łączna strata (szt.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($produkty as $produkt)
                        <tr>
                            <td class="border-b px-4 py-2">{{ $produkt->tw_nazwa }}</td>
                            <td class="border-b px-4 py-2">{{ $produkt->suma }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <a href="{{ route('straty.index') }}" class="text-blue-500 hover:underline mt-6 inline-block">← Wróć do listy strat</a>
    </div>
</x-layout>
