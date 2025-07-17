<!-- strona do wyświetlania zgłoszonych strat -->
<x-layout>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('straty.index') }}" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">Zobacz aktualne</a>
    </div>
        <h1 class="my-4 text-white text-2xl font-semibold text-center">Lista strat</h1>

        <div class="overflow-x-auto rounded-lg border border-gray-700">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">ID</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Data straty</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Opis</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Akcje</th>
                    </tr>
                    <tbody class="bg-gray-900 divide-y divide-gray-700">
                        @forelse($straty as $strata)
                        <tr class="hover:bg-gray-700 text-white text-center">
                            <td class="py-2 px-4 text-sm">{{ $strata->id }}</td>
                                <td class="py-2 px-4 text-sm">{{ $strata->data_straty }}</td>
                                <td class="py-2 px-4 text-sm">{{ $strata->opis ?? '—' }}</td>
                                <td class="py-2 px-4">
                                    <a href="{{ route('straty.show', $strata) }}"
                                     class="inline-block bg-inherit hover:bg-blue-700 text-white trxt-xl px-3 py-1 rounded aria-label="Szczegóły straty">
                                     👁️
                                    </a>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-white">Brak strat w archiwum</td>
                        </tr>
                        @endforelse
                    </tbody>
                </thead>
            </table>
        </div>

        <div class="d-flex justify-center mt-4">
            {{ $straty->links() }}
        </div>
</x-layout>
