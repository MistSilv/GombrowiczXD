<h1>Zamówienie #{{ $zamowienie->id }}</h1>

<table style="border-collapse: collapse; width: 100%; max-width: 600px;">
    <thead>
        <tr>
            <th style="border: 1px solid #333; padding: 8px; background-color: #f2f2f2; text-align: left;">Produkt</th>
            <th style="border: 1px solid #333; padding: 8px; background-color: #f2f2f2; text-align: right;">Ilość</th>
        </tr>
    </thead>
    <tbody>
        @foreach($zamowienie->produkty as $produkt)
        <tr>
            <td style="border: 1px solid #333; padding: 8px;">{{ $produkt->tw_nazwa }}</td>
            <td style="border: 1px solid #333; padding: 8px; text-align: right;">{{ $produkt->pivot->ilosc }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
