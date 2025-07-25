<x-layout>
<div class="container mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold mb-4 text-white">Lista wsadów</h1>
    

    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('wsady.archiwum') }}" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">Zobacz archiwum</a>
    </div>
    
    <div class="overflow-x-auto rounded-lg border border-gray-700">
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
                    <td class="py-2 px-4 text-sm">{{ $wsad->data_wsadu->format('Y-m-d H:i') }}</td>
                    <td class="py-2 px-4">
                        <a href="{{ route('wsady.show', $wsad->id) }}" 
                           class="inline-block bg-inherit hover:bg-blue-700 text-white text-xl px-3 py-1 rounded" 
                           aria-label="Szczegóły">
                            👁️
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-4 text-center text-white">Brak wsadów do wyświetlenia</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($wsady instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="d-flex justify-content-center mt-4">
            {{ $wsady->links() }}
        </div>
    @endif
</div>
</x-layout>
