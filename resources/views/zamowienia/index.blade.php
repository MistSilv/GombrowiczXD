<!-- strona do wyświetlania aktualnych zamówień -->
<x-layout>
    <div class="container">
        @auth
            @if(!auth()->user()->isSerwis())
               <div class="mb-6 flex flex-wrap gap-2">
                <a href="{{ route('zamowienia.archiwum', ['automat_id' => request('automat_id')]) }}" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">Archiwum zamówień</a>
                <a href="{{ route('zamowienia.podsumowanie.dzien', ['automat_id' => request('automat_id')]) }}" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">Podsumowanie dnia</a>
                <a href="{{ route('zamowienia.podsumowanie.tydzien', ['automat_id' => request('automat_id')]) }}" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">Podsumowanie tygodnia</a>
                <a href="{{ route('zamowienia.podsumowanie.miesiac', ['automat_id' => request('automat_id')]) }}" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">Podsumowanie miesiąca</a>
                <a href="{{ route('zamowienia.podsumowanie.rok', ['automat_id' => request('automat_id')]) }}" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">Podsumowanie roku</a>
                </div>
            @endif
        @endauth

        <h1 class="my-4 text-white text-2xl font-semibold text-center">Lista zamówień</h1>

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
                                <td class="py-2 px-4"
                                    <a href="{{ route('zamowienia.show', $zamowienie) }}" class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl rounded aria-label="Szczegóły"">👁️</a>
                                    <a href="{{ route('export.zamowienie', ['zamowienie_id' => $zamowienie->id, 'format' => 'csv']) }}" class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl rounded aria-label="CSV"">📄</a>
                                    <a href="{{ route('export.zamowienie', ['zamowienie_id' => $zamowienie->id, 'format' => 'xlsx']) }}" class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl rounded aria-label="XLSX"">📊</a>
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
</x-layout>