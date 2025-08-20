<x-layout>
    <div class="container">

        <h1 class="text-2xl font-bold mb-4 text-white">Archiwum wsadów</h1>

        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('wsady.index')}} " class="bg-emerald-900 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded">Zobacz aktualne</a>
        </div>

        <form method="GET" action="{{ route('wsady.archiwum') }}" class="flex flex-wrap md:flex-nowrap items-end gap-4 bg-gray-800 p-4 rounded mt-6">
            <div class="flex-1 min-w-[150px]">
                <label for="min_date" class="block text-white text-sm mb-1">Data od:</label>
                <input type="date" name="min_date" id="min_date" value="{{ request('min_date') }}"
                    class="w-full rounded px-3 py-2 bg-gray-700 text-white border border-gray-600">
            </div>

            <div class="flex-1 min-w-[150px]">
                <label for="max_date" class="block text-white text-sm mb-1">Data do:</label>
                <input type="date" name="max_date" id="max_date" value="{{ request('max_date') }}"
                    class="w-full rounded px-3 py-2 bg-gray-700 text-white border border-gray-600">
            </div>

            <div>
                <button type="submit"
                    class="bg-blue-800 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                    🔍 Szukaj
                </button>
            </div>
            @php
                $od = request('min_date') ?: now()->subMonth()->toDateString();
                $do = request('max_date') ?: now()->toDateString();
            @endphp

            <a href="{{ route('export.unified.range', ['typ' => 'wsady', 'zakres'=>'zakres', 'format' => 'xlsx', 'od' => $od, 'do' => $do]) }}"
                id="export-wsad-xlsx"
                class="bg-lime-900 hover:bg-lime-700 text-white font-semibold px-5 py-2 rounded shadow whitespace-nowrap">
                📊 Excel
            </a>

            <a href="{{ route('export.unified.range', ['typ' => 'wsady', 'zakres'=>'zakres', 'format' => 'csv', 'od' => $od, 'do' => $do]) }}"
                id="export-wsad-csv"
                class="bg-lime-800 hover:bg-lime-600 text-white font-semibold px-5 py-2 rounded shadow whitespace-nowrap">
                📄 CSV
            </a>

        </form>

        <div class="overflow-x-auto rounded-lg border border-gray-700 mt-6">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">ID</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Automat</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Data</th>
                        <th class="px-4 py-2 text-center text-sm font-medium text-white uppercase">Akcje</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-900 divide-y divide-gray-700">
                    @forelse($wsady as $wsad)
                    <tr class="hover:bg-gray-700 text-white text-center">
                        <td class="py-2 px-4 text-sm">{{ $wsad->id }}</td>
                        <td class="py-2 px-4 text-sm">{{ $wsad->automat->nazwa ?? 'Brak automatu' }}</td>
                        <td class="py-2 px-4 text-sm">{{ $wsad->data_wsadu->format('d-m-Y H:i') }}</td>
                        <td class="py-2 px-4">
                            <a href="{{ route('wsady.show', $wsad->id) }}" 
                            class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl px-3 py-1 rounded">👁️</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-white">Brak danych w archiwum</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-4">
            {{ $wsady->links('pagination::simple-tailwind') }}
        </div>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const exportWsadyXlsx = document.getElementById('export-wsady-xlsx');
            const exportWsadyCsv = document.getElementById('export-wsady-csv');
            const minDate = document.getElementById('min_date');
            const maxDate = document.getElementById('max_date');

            
        });
    </script>
</x-layout>
