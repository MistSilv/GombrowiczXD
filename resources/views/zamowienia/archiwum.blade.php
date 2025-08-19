<!-- strona do archiwum zamówień -->
<x-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-4 text-white">Archiwum zamówień</h1>
        
        <a href="{{ route('zamowienia.index') }}" class="bg-emerald-900 hover:bg-emerald-600 text-white font-bold py-2 px-4 rounded w-max">← Wróć do aktualnych</a>

        <form method="GET" action="{{ route('zamowienia.archiwum') }}"
            class="flex flex-wrap md:flex-nowrap items-end gap-4 bg-gray-800 p-4 rounded mt-6">

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

            <a href="{{ route('export.unified.range', ['typ' => 'zamowienia', 'zakres'=>'zakres', 'format' => 'xlsx', 'od' => $od, 'do' => $do]) }}"
                id="export-wsad-xlsx"
                class="bg-lime-900 hover:bg-lime-700 text-white font-semibold px-5 py-2 rounded shadow whitespace-nowrap">
                📊 Excel 
            </a>

            <a href="{{ route('export.unified.range', ['typ' => 'zamowienia', 'zakres'=>'zakres', 'format' => 'csv', 'od' => $od, 'do' => $do]) }}"
                id="export-wsad-csv"
                class="bg-lime-900 hover:bg-lime-700 text-white font-semibold px-5 py-2 rounded shadow whitespace-nowrap">
                📄 CSV
            </a>
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
                        <td class="py-2 px-4 text-sm">{{ $zamowienie->data_realizacji ?? '—' }}</td>
                        <td class="py-2 px-4">
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
            {{ $zamowienia->links('pagination::simple-tailwind') }}
        </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
        const exportXlsx = document.getElementById('export-xlsx');
        const exportCsv = document.getElementById('export-csv');
        const minDate = document.getElementById('min_date');
        const maxDate = document.getElementById('max_date');

        function updateLinks() {
            const od = minDate.value || '{{ now()->subMonth()->toDateString() }}';
            const doDate = maxDate.value || '{{ now()->toDateString() }}';

            // Zbuduj URL według wzoru:
            const baseUrl = "{{ url('/export/zamowienia/zakres') }}";
            exportXlsx.href = `${baseUrl}/xlsx/${od}/${doDate}`;
            exportCsv.href = `${baseUrl}/csv/${od}/${doDate}`;
        }

        minDate.addEventListener('change', updateLinks);
        maxDate.addEventListener('change', updateLinks);

        updateLinks(); // na start
    });


    </script>

</x-layout>
