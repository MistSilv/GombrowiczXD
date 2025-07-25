<!-- strona do wyświetlania zgłoszonych strat -->
<x-layout>
    <div class="container mx-auto px-4 py-6 ">

        <h1 class="text-2xl font-bold mb-4 text-white">Lista strat</h1>
        <div class="mb-6 flex flex-wrap gap-2">
                <a href="{{ route('straty.archiwum') }}" class="bg-rose-950 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">Zobacz archiwum</a>
            <a href="{{ route('straty.podsumowanie.dzien') }}" class="bg-rose-950 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
                Podsumowanie dnia
            </a>
            <a href="{{ route('straty.podsumowanie.tydzien') }}" class="bg-rose-950 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
                Podsumowanie tygodnia
            </a>
            <a href="{{ route('straty.podsumowanie.miesiac') }}" class="bg-rose-950 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
                Podsumowanie miesiąca
            </a>
            <a href="{{ route('straty.podsumowanie.rok') }}" class="bg-rose-950 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
                Podsumowanie roku
            </a>
        </div>
  
        

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
                            <td colspan="4" class="py-4 text-center text-white">Brak zgłoszonych strat</td>
                        </tr>
                        @endforelse
                    </tbody>
                </thead>
            </table>
        </div>

        <div class="d-flex justify-center mt-4">
            {{ $straty->links('pagination::simple-tailwind') }}
        </div>
</x-layout>
