<?php

namespace App\Exports;

use App\Models\Zamowienie;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ZamowienieExport implements FromCollection, WithHeadings
{
    protected $zamowienie;

    public function __construct(Zamowienie $zamowienie)
    {
        $this->zamowienie = $zamowienie;
    }

    public function collection()
    {
        // Upewnij się, że relacja produkty jest załadowana
        $this->zamowienie->load('produkty');

        // Mapujemy produkty do tablicy asocjacyjnej
        $mapped = $this->zamowienie->produkty->map(function ($produkt) {
            return [
                'Produkt' => $produkt->tw_nazwa,
                'Ilość' => $produkt->pivot->ilosc,
            ];
        });

        // Zwróć kolekcję
        return new Collection($mapped);
    }

    public function headings(): array
    {
        return ['Produkt', 'Ilość'];
    }
}
