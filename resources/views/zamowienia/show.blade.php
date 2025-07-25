<x-layout>
    <div class="container mx-auto px-4 py-6 text-white max-w-3xl bg-gray-900/70 rounded-xl">
        <h1 class="text-3xl font-extrabold text-center text-purple-300 mb-8">
            Szczegóły zamówienia #{{ $zamowienie->id }}
        </h1>

        <!-- Informacje o zamówieniu -->
        <div class="bg-gray-900/70 rounded-xl shadow-lg p-5 sm:p-6 mb-6 space-y-3 border border-gray-700">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-400 font-semibold">Data zamówienia:</span>
                <span class="text-base font-medium">{{ \Carbon\Carbon::parse($zamowienie->data_zamowienia)->format('Y-m-d H:i') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-400 font-semibold">Data realizacji:</span>
                <span class="text-base font-medium">
                    {{ $zamowienie->data_realizacji ?? '—' }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-400 font-semibold">Automat:</span>
                <span class="text-base font-medium">
                    @if ($zamowienie->automat)
                        {{ $zamowienie->automat->nazwa }}
                    @else
                        <span class="italic text-gray-400">Zamówienie ogólne</span>
                    @endif
                </span>
            </div>
        </div>

        <!-- Lista produktów -->
        <div class="bg-gray-900/70 rounded-xl shadow-lg p-5 sm:p-6 border border-gray-700">
            <h2 class="text-xl sm:text-2xl font-semibold text-purple-200 mb-4">
                Produkty w zamówieniu
            </h2>

            @if ($zamowienie->produkty->isEmpty())
                <p class="text-gray-400 italic">Brak produktów w zamówieniu.</p>
            @else
                <ul class="space-y-3">
                    @foreach ($zamowienie->produkty as $produkt)
                        <li class="bg-gray-800/50 border border-gray-700 rounded-md px-4 py-2 flex justify-between items-center">
                            <span class="text-white">{{ $produkt->tw_nazwa }}</span>
                            <span class="font-semibold text-purple-400">{{ $produkt->pivot->ilosc }} szt.</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Powrót jako przycisk -->
        <div class="mt-8 text-center">
            <a href="{{ route('zamowienia.index', ['automat_id' => $zamowienie->automat_id]) }}"
               class="inline-block px-6 py-2 bg-rose-950 hover:bg-red-900 text-white font-semibold rounded-lg shadow transition duration-200">
                ← Powrót do listy
            </a>
        </div>
    </div>
</x-layout>
