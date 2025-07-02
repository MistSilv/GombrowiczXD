<x-layout>
    <h1 class="text-3xl font-bold mb-6">Produkty niewłasne</h1>

    <table class="min-w-full bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-6 text-left font-semibold text-gray-700">Nazwa produktu</th>
                <th class="py-3 px-6 text-left font-semibold text-gray-700">Łączna ilość (wsad)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produkty as $produkt)
                <tr class="border-b last:border-0 hover:bg-gray-100">
                    <td class="py-3 px-6">{{ $produkt->tw_nazwa }}</td>
                    <td class="py-3 px-6">{{ $produkt->suma_ilosci }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        // Pobierz listę ID produktów do eksportu jako ciąg rozdzielony przecinkami
        $produktIds = $produkty->pluck('id')->implode(',');
    @endphp

    <a href="{{ route('export.produkty.niewlasne', ['ids' => $produktIds, 'format' => 'xlsx']) }}" 
       class="inline-block mr-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
       Eksportuj XLSX
    </a>

    <a href="{{ route('export.produkty.niewlasne', ['ids' => $produktIds, 'format' => 'csv']) }}" 
       class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
       Eksportuj CSV
    </a>

    <a href="{{ url('/welcome') }}" 
       class="mt-8 inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded transition">
       Powrót
    </a>
</x-layout>
