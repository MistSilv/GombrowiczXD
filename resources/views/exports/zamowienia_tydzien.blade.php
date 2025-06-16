<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>Produkt</th>
            <th>Ilość</th>
        </tr>
    </thead>
    <tbody>
        @foreach($produktyPerDay as $dzien => $produkty)
            <tr>
                <td colspan="3" style="font-weight:bold; background-color:#ddd;">
                    {{ \Carbon\Carbon::parse($dzien)->locale('pl')->isoFormat('dddd DD.MM.YYYY') }}
                </td>
            </tr>
            @foreach($produkty as $produkt)
                <tr>
                    <td></td>
                    <td>{{ $produkt['tw_nazwa'] }}</td>
                    <td>{{ $produkt['suma'] }}</td>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td colspan="3" style="font-weight:bold; background-color:#ccc;">
                Suma tygodnia ({{ $start }} - {{ $end }})
            </td>
        </tr>
        @foreach($sumyTygodnia as $produkt)
            <tr>
                <td></td>
                <td>{{ $produkt->tw_nazwa }}</td>
                <td>{{ $produkt->suma }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
