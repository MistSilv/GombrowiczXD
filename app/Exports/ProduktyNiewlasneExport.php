<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class ProduktyNiewlasneExport implements FromCollection, WithHeadings
{
    protected $produkty;

    public function __construct(Collection $produkty)
    {
        $this->produkty = $produkty;
    }

    public function collection()
    {
        return $this->produkty->map(function ($produkt) {
            return [
                'Nazwa produktu' => $produkt->tw_nazwa,
                'Łączna ilość (wsad)' => $produkt->suma_ilosci,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nazwa produktu',
            'Łączna ilość (wsad)',
        ];
    }
}
