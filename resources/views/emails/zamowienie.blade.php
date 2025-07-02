<h1>Zamówienie #{{ $zamowienie->id }}</h1>
<h3>Automat: {{ $zamowienie->automat->nazwa ?? 'Brak danych' }}</h3>

<table width="100%" style="border-collapse: collapse;">
    <thead>
        <tr>
            <th style="border: 1px solid black; padding: 5px;">Produkt</th>
            <th style="border: 1px solid black; padding: 5px;">Ilość</th>
        </tr>
    </thead>
    <tbody>
        @php $suma = 0; @endphp
        @foreach($zamowienie->produkty as $produkt)
            <tr>
                <td style="border: 1px solid black; padding: 5px;">{{ $produkt->tw_nazwa }}</td>
                <td style="border: 1px solid black; padding: 5px; text-align: right;">{{ $produkt->pivot->ilosc }}</td>
            </tr>
            @php $suma += $produkt->pivot->ilosc; @endphp
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="border: 1px solid black; padding: 5px; font-weight: bold;">Suma</td>
            <td style="border: 1px solid black; padding: 5px; text-align: right; font-weight: bold;">{{ $suma }}</td>
        </tr>
    </tfoot>
</table>
