<?php

namespace App\Exports;

use App\Models\Zamowienie;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ZamowienieExport implements FromCollection, WithHeadings, WithEvents, WithColumnFormatting
{
    protected $zamowienie;

    public function __construct($zamowienie)
    {
        $this->zamowienie = $zamowienie;
    }

    public function collection()
    {
        $this->zamowienie->load(['produkty' => function($query) {
            $query->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
                  ->select('produkty.id', 'tw_nazwa', 'ean_codes.kod_ean as ean');
        }]);

        $mapped = $this->zamowienie->produkty->map(function ($produkt) {
            return [
                'Produkt' => $produkt->tw_nazwa,
                'Kod EAN' => $produkt->ean ? (int)$produkt->ean : 'Brak kodu', // Rzutowanie na integer
                'Ilość' => $produkt->pivot->ilosc,
            ];
        })->toArray();

        return new Collection($mapped);
    }

    public function headings(): array
    {
        return [
            'Produkt', 
            'Kod EAN',
            'Ilość'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '0', // Format liczbowy bez miejsc po przecinku
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Ustaw szerokość kolumn
                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(10);

                // Wymuś format liczbowy dla kolumny EAN
                $sheet->getStyle('B2:B' . ($this->zamowienie->produkty->count() + 1))
                     ->getNumberFormat()
                     ->setFormatCode('0');

                $rowCount = $this->zamowienie->produkty->count() + 1;

                $sumCell = 'C' . ($rowCount + 1);
                $sheet->setCellValue($sumCell, "=SUM(C2:C{$rowCount})");
                $sheet->getStyle($sumCell)->getFont()->setBold(true);

                $sheet->setCellValue('B' . ($rowCount + 1), 'Suma:');
                $sheet->getStyle('B' . ($rowCount + 1))->getFont()->setBold(true);
                
                // Styl nagłówków
                $sheet->getStyle('A1:C1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '4F81BD'],
                    ],
                ]);
            },
        ];
    }
}