<x-layout>
    <div class="container mx-auto px-4 py-6 text-white max-w-3xl">
        <h1 class="text-3xl font-bold mb-6 text-center">Szczegóły zamówienia #{{ $zamowienie->id }}</h1>

        <div class="bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <p class="mb-2"><strong>Data zamówienia:</strong> {{ \Carbon\Carbon::parse($zamowienie->data_zamowienia)->format('Y-m-d H:i') }}</p>
            <p class="mb-2"><strong>Data realizacji:</strong> {{ $zamowienie->data_realizacji ?? '—' }}</p>
            <p><strong>Automat:</strong> 
                @if ($zamowienie->automat)
                    {{ $zamowienie->automat->nazwa }}
                @else
                    Zamówienie ogólne
                @endif
            </p>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 border-b border-gray-600 pb-2">Produkty w zamówieniu:</h2>
            @if ($zamowienie->produkty->isEmpty())
                <p>Brak produktów w zamówieniu.</p>
            @else
                <ul class="list-disc pl-6 space-y-2">
                    @foreach ($zamowienie->produkty as $produkt)
                        <li>{{ $produkt->tw_nazwa }} — <span class="font-semibold">{{ $produkt->pivot->ilosc }} szt.</span></li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('zamowienia.index', ['automat_id' => $zamowienie->automat_id]) }}" 
               class="text-blue-500 hover:underline mt-6 inline-block">
                ← Wróć do listy
            </a>
        </div>
    </div>
</x-layout>
