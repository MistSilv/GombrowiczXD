<?php

namespace App\Exports;

use App\Models\Zamowienie;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ZamowienieExport implements FromCollection, WithHeadings, WithEvents
{
    protected $zamowienie;

    public function __construct($zamowienie)
    {
        $this->zamowienie = $zamowienie;
    }

    public function collection()
    {
        $this->zamowienie->load('produkty');

        $mapped = $this->zamowienie->produkty->map(function ($produkt) {
            return [
                'Produkt' => $produkt->tw_nazwa,
                'Ilość' => $produkt->pivot->ilosc,
            ];
        })->toArray();

        return new Collection($mapped);
    }

    public function headings(): array
    {
        return ['Produkt', 'Ilość'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach (range('A', 'B') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $rowCount = $this->zamowienie->produkty->count() + 1;

                $sumCell = 'B' . ($rowCount + 1);
                $sheet->setCellValue($sumCell, "=SUM(B2:B{$rowCount})");
                $sheet->getStyle($sumCell)->getFont()->setBold(true);

                $sheet->setCellValue('A' . ($rowCount + 1), 'Suma:');
                $sheet->getStyle('A' . ($rowCount + 1))->getFont()->setBold(true);
            },
        ];
    }
}
