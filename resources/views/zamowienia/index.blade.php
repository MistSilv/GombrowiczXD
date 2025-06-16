<x-layout>
    <div class="container">
        <h1 class="text-2xl font-bold mb-4">Aktualne zamówienia</h1>

        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('zamowienia.archiwum') }}" class="btn btn-secondary">Archiwum zamówień</a>
            <a href="{{ route('zamowienia.podsumowanie.dzien') }}" class="btn btn-primary">Podsumowanie dnia</a>
            <a href="{{ route('zamowienia.podsumowanie.tydzien') }}" class="btn btn-primary">Podsumowanie tygodnia</a>
            <a href="{{ route('zamowienia.podsumowanie.miesiac') }}" class="btn btn-primary">Podsumowanie miesiąca</a>
            <a href="{{ route('zamowienia.podsumowanie.rok') }}" class="btn btn-primary">Podsumowanie roku</a>
        </div>

        @if ($zamowienia->isEmpty())
            <p>Brak aktualnych zamówień.</p>
        @else
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
                        <tr>
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
        @endif
    </div>
</x-layout>
