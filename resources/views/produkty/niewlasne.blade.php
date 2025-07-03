<x-layout>
    <h1 class="text-3xl font-bold mb-6">Produkty niewłasne</h1>

    <table class="min-w-full bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-6 text-left font-semibold text-gray-700">Nazwa produktu</th>
                <th class="py-3 px-6 text-left font-semibold text-gray-700">Łączna ilość wsadzonych</th>
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
        $produktIds = $produkty->pluck('id')->implode(',');
    @endphp

    <div class="mt-9 text-center flex flex-col gap-4 sm:flex-row sm:justify-center sm:gap-6">
        <a href="{{ route('export.produkty.niewlasne', ['ids' => $produktIds, 'format' => 'xlsx']) }}"
           class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
            Eksportuj XLSX
        </a>

        <a href="{{ route('export.produkty.niewlasne', ['ids' => $produktIds, 'format' => 'csv']) }}"
           class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
            Eksportuj CSV
        </a>

       <a href="{{ route('produkty.zamowienie.nowe') }}"
            class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
            Nowe zamówienie
        </a>

     

        <a href="{{ url('/welcome') }}"
           class="bg-slate-800 hover:bg-red-900 text-white font-bold py-2 px-4 rounded">
            Powrót
        </a>
    </div>
</x-layout>
