<table>
    <thead>
        <tr>
            <th>Produkt</th>
            <th>Ilość</th>
        </tr>
    </thead>
    <tbody>
        @foreach($produkty as $produkt)
            <tr>
                <td>{{ $produkt->tw_nazwa }}</td>
                <td>{{ $produkt->suma }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
