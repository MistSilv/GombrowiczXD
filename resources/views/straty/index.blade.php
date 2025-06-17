<x-layout>
    <x-subnav />
    <div class="container">
        <h1 class="text-2xl font-bold mb-4 text-white">Zgłoszone straty</h1>

        @if ($straty->isEmpty())
        <p>Brak zgłoszonych strat.</p>
        @else
            <div class="overflow-x-auto w-full overflow-visible">
                <table class="table-auto w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100 text-black">
                            <th class="border px-4 py-2">ID</th>
                            <th class="border px-4 py-2">Data straty</th>
                            <th class="border px-4 py-2">Opis</th>
                            <th class="border px-4 py-2">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($straty as $strata)
                            <tr class="text-white">
                                <td class="border px-4 py-2">{{ $strata->id }}</td>
                                <td class="border px-4 py-2">{{ $strata->data_straty }}</td>
                                <td class="border px-4 py-2">{{ $strata->opis ?? '—' }}</td>
                                <td class="border px-4 py-2">
                                    <a href="{{ route('straty.show', $strata) }}" class="text-blue-500 hover:underline">Szczegóły</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layout>
