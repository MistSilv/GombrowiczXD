<x-layout>
    <div class="container mx-auto px-4 py-6 max-w-3xl text-white">
        <h1 class="text-3xl font-bold mb-6 text-center">Szczegóły straty #{{ $strata->id }}</h1>

        <div class="bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <p class="mb-2"><strong>Data straty:</strong> {{ $strata->data_straty }}</p>
            <p class="mb-2"><strong>Automat:</strong> {{ $strata->automat->nazwa }} – {{ $strata->automat->lokalizacja }}</p>
            <p><strong>Opis:</strong> {{ $strata->opis ?? '—' }}</p>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 border-b border-gray-600 pb-2">Produkty objęte stratą:</h2>
            @if ($strata->produkty->isEmpty())
                <p>Brak produktów.</p>
            @else
                <ul class="list-disc pl-6 space-y-2">
                    @foreach ($strata->produkty as $produkt)
                        <li>{{ $produkt->tw_nazwa }} — <span class="font-semibold">{{ $produkt->pivot->ilosc }} szt.</span></li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('straty.index') }}"
               class="text-blue-500 hover:underline mt-6 inline-block">
                ← Wróć do listy strat
            </a>
        </div>
    </div>
</x-layout>
