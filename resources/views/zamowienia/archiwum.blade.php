<!-- strona do archiwum zamówień -->
<x-layout>
    <div class="container">
        <h1 class="text-2xl font-bold mb-4 text-white">Archiwum zamówień</h1>
        <a href="{{ route('zamowienia.index') }}" class="bg-slate-800 hover:bg-blue-900 text-white font-bold py-2 px-4 rounded">← Wróć do aktualnych</a>
        
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
