<x-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="my-4 text-white text-3xl font-semibold text-center">Szczegóły wsadu #{{ $wsad->id }}</h1>

        <div class="bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h5 class="text-xl font-semibold text-white mb-4 border-b border-gray-600 pb-2">Informacje podstawowe</h5>
            <p class="text-white mb-2"><strong>Automat:</strong> {{ $wsad->automat->nazwa ?? 'Brak danych' }}</p>
            <p class="text-white"><strong>Data wsadu:</strong> {{ $wsad->data_wsadu ? $wsad->data_wsadu->format('Y-m-d H:i') : 'Brak danych' }}</p>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-md p-6">
            <h5 class="text-xl font-semibold text-white mb-4 border-b border-gray-600 pb-2">Lista produktów</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900">
                        <tr>
                            <th class="px-4 py-2 text-left text-white uppercase tracking-wider">Produkt</th>
                            <th class="px-4 py-2 text-left text-white uppercase tracking-wider">Ilość</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($wsad->produkty as $produkt)
                            <tr class="hover:bg-gray-700">
                                <td class="px-4 py-2 text-white">{{ $produkt->tw_nazwa }}</td>
                                <td class="px-4 py-2 text-white">{{ $produkt->pivot->ilosc }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('wsady.index') }}" 
               class="text-blue-500 hover:underline mt-6 inline-block">
                ← Wróć do listy wsadów
            </a>
        </div>
    </div>
</x-layout>
