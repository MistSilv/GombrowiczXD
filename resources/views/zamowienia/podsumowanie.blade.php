<x-layout>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Podsumowanie zamówień ({{ $typ }})</h1>
        <p class="mb-6">Okres: <strong>{{ $okres }}</strong></p>

        @if($produkty->isEmpty())
            <p>Brak zamówień w tym okresie.</p>
        @else
            @php
                $zakresMap = [
                    'Dzień' => 'dzien',
                    'Tydzień' => 'tydzien',
                    'Miesiąc' => 'miesiac',
                    'Rok' => 'rok',
                ];
                $zakresSlug = $zakresMap[$typ] ?? 'dzien';
                $dateForUrl = \Illuminate\Support\Str::before($okres, ' do'); // dla tygodnia obetnij zakres
            @endphp

            <div class="mb-4 flex gap-3">
                <a href="{{ route('zamowienia.export', ['zakres' => $zakresSlug, 'format' => 'xlsx', 'date' => $dateForUrl]) }}"
                   class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">
                    Eksportuj do Excel (.xlsx)
                </a>
                <a href="{{ route('zamowienia.export', ['zakres' => $zakresSlug, 'format' => 'csv', 'date' => $dateForUrl]) }}"
                   class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                    Eksportuj do CSV
                </a>
            </div>

            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">Produkt</th>
                        <th class="py-2 px-4 border-b">Łączna ilość</th>
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

        <a href="{{ route('zamowienia.index') }}" class="text-blue-500 hover:underline mt-6 inline-block">← Wróć do listy zamówień</a>
    </div>
</x-layout>
