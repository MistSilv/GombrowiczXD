<!-- strona do wyświetlania aktualnych zamówień -->
<x-layout>
    <div class="container mx-auto px-4 py-6">

        
        @auth
            @if(!auth()->user()->isSerwis())
                <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex flex-col">
                            <h1 class="text-2xl font-bold mb-4 text-white">Lista zamówień</h1>
                            <a href="{{ route('zamowienia.archiwum', ['automat_id' => request('automat_id')]) }}"
                            class="bg-emerald-900 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded w-max">
                            Archiwum zamówień
                        </a>
                        </div>
                    <div>
                        <h1 class="text-2xl font-bold mb-4 text-white"">Podsumowanie dla:</h1>
                        <div class="flex flex-wrap gap-2">
                        <a href="{{ route('export.unified.day', ['typ' => 'zamowienia', 'zakres' => 'dzien', 'od' => null, 'format' => null]) }}?automat_id={{ request('automat_id') }}"
                            class="bg-emerald-900 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded w-max">
                            Dnia
                        </a>
                        <a href="{{ route('export.unified.day', ['typ' => 'zamowienia', 'zakres' => 'tydzien', 'od' => null, 'format' => null]) }}?automat_id={{ request('automat_id') }}"
                            class="bg-emerald-900 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded w-max">
                            Tygodnia
                        </a>
                        <a href="{{ route('export.unified.day', ['typ' => 'zamowienia', 'zakres' => 'miesiac', 'od' => null, 'format' => null]) }}?automat_id={{ request('automat_id') }}"
                            class="bg-emerald-900 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded w-max">
                            Miesiąca
                        </a>
                        <a href="{{ route('export.unified.day', ['typ' => 'zamowienia', 'zakres' => 'rok', 'od' => null, 'format' => null]) }}?automat_id={{ request('automat_id') }}"
                            class="bg-emerald-900 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded w-max">
                            Roku
                        </a>
                        </div>
                    </div>
                </div>
            @endif
        @endauth


        <div class="overflow-x-auto rounded-lg border border-gray-700">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">ID</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Data zamówienia</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Data realizacji</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Akcje</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-900 divide-y divide-gray-700">
                    @forelse ( $zamowienia as $zamowienie )
                    <tr class="hover:bg-gray-700 text-white text-center">
                        <td class="py-2 px-4 text-smą">{{ $zamowienie->id }}</td>
                                <td class="py-2 px-4 text-sm">{{ \Carbon\Carbon::parse($zamowienie->data_zamowienia)->format('Y.m.d H:i') }}</td>
                                <td class="py-2 px-4 text-sm">{{ $zamowienie->data_realizacji ?? '—' }}</td>
                                <td class="py-2 px-4">
                                    <a href="{{ route('zamowienia.show', $zamowienie) }}" class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl rounded">👁️</a>
                                    <a href="{{ route('export.zamowienie', ['zamowienie_id' => $zamowienie->id, 'format' => 'csv']) }}" class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl rounded ">📄</a>
                                    <a href="{{ route('export.zamowienie', ['zamowienie_id' => $zamowienie->id, 'format' => 'xlsx']) }}" class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl rounded ">📊</a>
                                    <a href="{{ route('zamowienia.print', $zamowienie) }}" target="_blank"class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl rounded">🖨️</a>
                                </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-white">Brak zamówień do wyświetlenia</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $zamowienia->links('pagination::simple-tailwind') }}
        </div>
</x-layout>