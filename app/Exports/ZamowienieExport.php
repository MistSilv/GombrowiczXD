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
                'Produkt' => mb_convert_encoding($produkt->tw_nazwa ?? '', 'UTF-8', 'UTF-8'),
                'Kod EAN' => $produkt->ean ? (string)$produkt->ean : 'Brak kodu',
                'Ilość'   => $produkt->pivot->ilosc,
            ];
        })->toArray();

        return new Collection($mapped);
    }

    public function headings(): array
    {
        return ['Produkt', 'Kod EAN', 'Ilość'];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER, // Liczba bez zer po przecinku
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getColumnDimension('A')->setWidth(30);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(10);

                $count = $this->zamowienie->produkty->count();
                $rowCount = $count + 1;

                $sheet->getStyle('B2:B' . $rowCount)
                      ->getNumberFormat()
                      ->setFormatCode(NumberFormat::FORMAT_NUMBER);

                $sheet->setCellValue('B' . ($rowCount + 1), 'Suma:');
                $sheet->getStyle('B' . ($rowCount + 1))->getFont()->setBold(true);

                $sheet->setCellValue('C' . ($rowCount + 1), "=SUM(C2:C{$rowCount})");
                $sheet->getStyle('C' . ($rowCount + 1))->getFont()->setBold(true);

                $sheet->getStyle('A1:C1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['rgb' => '4F81BD'],
                    ],
                ]);
            },
        ];
    }
}
