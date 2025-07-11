<x-layout>
    <div class="container text-white">
        <h1 class="text-2xl font-bold mb-4">Szczegóły zamówienia #{{ $zamowienie->id }}</h1>

        <p><strong>Data zamówienia:</strong> {{ \Carbon\Carbon::parse($zamowienie->data_zamowienia)->format('Y-m-d H:i') }}</p>
        <p><strong>Data realizacji:</strong> {{ $zamowienie->data_realizacji ?? '—' }}</p>

        <p><strong>Automat:</strong> 
            @if ($zamowienie->automat)
                {{ $zamowienie->automat->nazwa }}
            @else
                Zamówienie ogólne
            @endif
        </p>

        <h2 class="text-xl font-semibold mt-6 mb-2">Produkty w zamówieniu:</h2>
        @if ($zamowienie->produkty->isEmpty())
            <p>Brak produktów w zamówieniu.</p>
        @else
            <ul class="list-disc pl-6">
                @foreach ($zamowienie->produkty as $produkt)
                    <li>
                        {{ $produkt->tw_nazwa }} — {{ $produkt->pivot->ilosc }} szt.
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ route('zamowienia.index', ['automat_id' => $zamowienie->automat_id]) }}" class="text-blue-500 hover:underline mt-6 inline-block">← Wróć do listy</a>
    </div>
</x-layout>
