<x-layout>
    <h1 class="text-3xl font-bold mb-6">Edytuj ilości produktów niewłasnych</h1>

    <form action="{{ route('produkty.niewlasne.zapisz') }}" method="POST" class="space-y-4">
        @csrf

        <table class="min-w-full bg-white rounded shadow overflow-hidden">
            <thead class="bg-gray-200">
                <tr>
                    <th class="py-3 px-6 text-left font-semibold text-gray-700">Nazwa produktu</th>
                    <th class="py-3 px-6 text-left font-semibold text-gray-700">Ilość</th>
                </tr>
            </thead>
            <tbody>
                @foreach($produkty as $produkt)
                    <tr class="border-b last:border-0 hover:bg-gray-100">
                        <td class="py-3 px-6">{{ $produkt->tw_nazwa }}</td>
                        <td class="py-3 px-6">
                            <input type="number" name="ilosci[{{ $produkt->id }}]" step="0.01"
                                   value="{{ $produkt->suma_ilosci }}" 
                                   class="border rounded px-3 py-1 w-32">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6 text-center">
            <button type="submit" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
                Zapisz zmiany
            </button>
            <a href="{{ url('/welcome') }}" class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded ml-4">
                Powrót
            </a>
        </div>
    </form>
</x-layout>
