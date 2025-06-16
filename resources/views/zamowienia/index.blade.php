<x-layout>
    <div class="container">
        <h1 class="text-2xl font-bold mb-4 text-white">Aktualne zamówienia</h1>

        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('zamowienia.archiwum') }}" class="bg-slate-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">Archiwum zamówień</a>
            <a href="{{ route('zamowienia.podsumowanie.dzien') }}" class="bg-slate-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">Podsumowanie dnia</a>
            <a href="{{ route('zamowienia.podsumowanie.tydzien') }}" class="bg-slate-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">Podsumowanie tygodnia</a>
            <a href="{{ route('zamowienia.podsumowanie.miesiac') }}" class="bg-slate-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">Podsumowanie miesiąca</a>
            <a href="{{ route('zamowienia.podsumowanie.rok') }}" class="bg-slate-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">Podsumowanie roku</a>
        </div>

        @if ($zamowienia->isEmpty())
        <p>Brak aktualnych zamówień.</p>
        @else
            <div class="overflow-x-auto w-full overflow-visible">
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
                                <td class="border px-4 py-2">{{ $zamowienie->data_zamowienia }}</td>
                                <td class="border px-4 py-2">{{ $zamowienie->data_realizacji ?? '—' }}</td>
                                <td class="border px-4 py-2">
                                    <a href="{{ route('zamowienia.show', $zamowienie) }}" class="text-blue-500 hover:underline">Szczegóły</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layout>
