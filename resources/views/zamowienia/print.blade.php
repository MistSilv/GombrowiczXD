<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zamówienie #{{ $zamowienie->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body class="bg-white text-black p-8 font-sans">
    <!-- Nagłówek -->
    <div class="flex justify-between items-start mb-8 border-b pb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Zamówienie #{{ $zamowienie->id }}</h1>
            <p class="text-sm text-gray-500">Wydrukowano: {{ now()->format('Y-m-d H:i') }}</p>
        </div>
        <div class="text-right text-sm text-gray-600">
            <p>
                <span class="font-semibold">Automat:</span> 
                {{ $zamowienie->automat?->nazwa ?? '—' }}
            </p>
            <p>
                <span class="font-semibold">Data zamówienia:</span> 
                {{ \Carbon\Carbon::parse($zamowienie->data_zamowienia)->format('d.m.Y H:i') }}
            </p>
            <p>
                <span class="font-semibold">Data realizacji:</span> 
                {{ $zamowienie->data_realizacji ?? '—' }}
            </p>
        </div>
    </div>

    <!-- Produkty -->
    <h2 class="text-2xl font-semibold mb-4 text-gray-700">Lista produktów</h2>
    <div class="overflow-hidden rounded-lg shadow">
        <table class="w-full border border-gray-300 text-sm">
            <thead class="bg-gray-200 text-gray-700 uppercase text-xs tracking-wider">
                <tr>
                    <th class="border px-4 py-3 text-left">Produkt</th>
                    <th class="border px-4 py-3 text-center w-32">Ilość</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($zamowienie->produkty as $produkt)
                    @php $total += $produkt->pivot->ilosc; @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="border px-4 py-2">{{ $produkt->tw_nazwa }}</td>
                        <td class="border px-4 py-2 text-center font-medium">{{ $produkt->pivot->ilosc }}</td>
                    </tr>
                @endforeach
                <!-- Wiersz sumy -->
                <tr class="bg-gray-100 font-semibold">
                    <td class="border px-4 py-2 text-right">Łącznie:</td>
                    <td class="border px-4 py-2 text-center">{{ $total }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
