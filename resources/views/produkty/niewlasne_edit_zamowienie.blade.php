<x-layout>
<h1 class="text-3xl font-bold mb-6 text-white">Edytuj ilości produktów dla zamówienia</h1>

@if(session('success'))
    <div class="bg-green-200 text-green-800 px-4 py-2 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('produkty.zamowienie.zapisz') }}" method="POST" class="space-y-4">
    @csrf
    <input type="hidden" name="zamowienieId" value="{{ $zamowienieId ?? '' }}">

    <table class="min-w-full bg-white rounded shadow overflow-hidden">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-6 text-left font-bold text-gray-700">Nazwa produktu</th>
                <th class="py-3 px-6 text-left font-bold text-gray-700">Ilość</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produkty as $produkt)
                <tr class="border-b last:border-0 hover:bg-gray-100">
                    <td class="py-3 px-6">{{ $produkt->tw_nazwa }}</td>
                    <td class="py-3 px-6">
                        <input type="number" name="ilosci[{{ $produkt->id }}]" step="1" min="0" max="2147483647"
                               value="{{ $produkt->ilosc ?? 0 }}" class="border rounded px-3 py-1 w-32">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>s

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
