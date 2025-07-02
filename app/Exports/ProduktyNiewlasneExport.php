<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProduktyNiewlasneExport implements FromCollection, WithHeadings
{
    protected $produkty;

    public function __construct($produkty)
    {
        $this->produkty = $produkty;
    }

    public function collection()
    {
        // Zwracamy kolekcję produktów do eksportu
        return $this->produkty->map(function ($produkt) {
            return [
                'tw_nazwa' => $produkt->tw_nazwa,
                'suma_ilosci' => $produkt->suma_ilosci,
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
