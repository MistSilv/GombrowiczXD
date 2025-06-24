<x-layout>
    <div class="container text-white">
        <h1 class="text-2xl font-bold mb-4">Szczegóły straty #{{ $strata->id }}</h1>

        <p><strong>Data straty:</strong> {{ $strata->data_straty }}</p>
        <p><strong>Automat:</strong> {{ $strata->automat->nazwa }} – {{ $strata->automat->lokalizacja }}</p>
        <p><strong>Opis:</strong> {{ $strata->opis ?? '—' }}</p>

        <h2 class="text-xl font-semibold mt-6 mb-2">Produkty objęte stratą:</h2>
        @if ($strata->produkty->isEmpty())
            <p>Brak produktów.</p>
        @else
            <ul class="list-disc pl-6">
                @foreach ($strata->produkty as $produkt)
                    <li>
                        {{ $produkt->tw_nazwa }} — {{ $produkt->pivot->ilosc }} szt.
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ route('straty.index') }}" class="text-blue-500 hover:underline mt-6 inline-block">← Wróć do listy strat</a>
    </div>
</x-layout>
