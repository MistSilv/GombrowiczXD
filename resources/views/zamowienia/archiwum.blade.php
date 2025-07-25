<!-- strona do archiwum zamówień -->
<x-layout>
    <div class="container">
        
        <h1 class="text-2xl font-bold mb-4 text-white">Archiwum zamówień</h1>
        
        <a href="{{ route('zamowienia.index') }}" class="bg-rose-950 hover:bg-red-900 text-white font-bold py-2 px-4 rounded"> Wróć do aktualnych</a>

        <form method="GET" action="{{ route('zamowienia.archiwum') }}" class="flex flex-wrap items-center gap-4 bg-gray-800 p-4 rounded mt-6">
            <div>
                <label for="min_date" class="block text-white text-sm mb-1">Data od:</label>
                <input type="date" name="min_date" id="min_date" value="{{ request('min_date') }}"
                    class="rounded px-3 py-2 bg-gray-700 text-white border border-gray-600">
            </div>

            <div>
                <label for="max_date" class="block text-white text-sm mb-1">Data do:</label>
                <input type="date" name="max_date" id="max_date" value="{{ request('max_date') }}"
                    class="rounded px-3 py-2 bg-gray-700 text-white border border-gray-600">
            </div>

            <div class="self-end">
                <button type="submit"
                    class="bg-blue-700 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">
                    🔍 Szukaj
                </button>
            </div>
        </form>

        <div class="overflow-x-auto rounded-lg border border-gray-700 mt-6">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-800">
                    <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">ID</th>
                    <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Data zamówienia</th>
                    <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Data realizacji</th>
                    <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Akcje</th>
                </thead>
                <tbody class="bg-gray-900 divide-y divide-gray-700">
                    @forelse ($zamowienia as $zamowienie)
                    <tr class="hover:bg-gray-700 text-white text-center">
                        <td class="py-2 px-4 text-sm">{{ $zamowienie->id }}</td>
                        <td class="py-2 px-4 text-sm">{{ $zamowienie->data_zamowienia }}</td>
                        <td class="py-2 px-4 text-sm"">{{ $zamowienie->data_realizacji ?? '—' }}</td>
                        <td class="py-2 px-4"">
                            <a href="{{ route('zamowienia.show', $zamowienie) }}" class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl rounded aria-label="Szczegóły"">👁️</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-white">Brak zamówień</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $zamowienia->links() }}
        </div>
</x-layout>
