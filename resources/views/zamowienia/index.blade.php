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

        <h1 class="text-2xl font-bold mb-4 text-white">
            Aktualne zamówienia
            @if(request('automat_id'))
                dla automatu #{{ request('automat_id') }}
            @endif
        </h1>

        @if ($zamowienia->isEmpty())
            <p class="text-white">Brak aktualnych zamówień.</p>
        @else
            <div class="overflow-x-auto w-full overflow-visible">
                <!-- tabela z zamówieniami -->
                <table class="table-auto w-full border-collapse border border-gray-300"> 
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border px-4 py-2">ID</th>
                            <th class="border px-4 py-2">Data zamówienia</th>
                            <th class="border px-4 py-2">Data realizacji</th>
                            <th class="border px-4 py-2">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($zamowienia as $zamowienie)
                            <tr class="text-white">
                                <td class="border px-4 py-2">{{ $zamowienie->id }}</td>
                                <td class="border px-4 py-2">{{ \Carbon\Carbon::parse($zamowienie->data_zamowienia)->format('d.m.Y H:i') }}</td>
                                <td class="border px-4 py-2">{{ $zamowienie->data_realizacji ?? '—' }}</td>
                                <td class="border px-4 py-2">
                                    <a href="{{ route('zamowienia.show', $zamowienie) }}" class="text-violet-900 hover:underline">ℹ️</a>
                                    <a href="{{ route('export.zamowienie', ['zamowienie_id' => $zamowienie->id, 'format' => 'csv']) }}" class="text-violet-900 hover:underline">📥 CSV</a>
                                    <a href="{{ route('export.zamowienie', ['zamowienie_id' => $zamowienie->id, 'format' => 'xlsx']) }}" class="text-violet-900 hover:underline">📊 XLSX</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layout>