<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function exportZamowienia($zakres, $date = null, $format = 'xlsx')
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        if ($zakres === 'tydzien') {
            return $this->exportTygodniowy($date, $format);
        }
        if ($zakres === 'dzien') {
            return $this->exportDzienny($date, $format);
        }
        if ($zakres === 'miesiac') {
            return $this->exportMiesieczny($date, $format);
        }
        if ($zakres === 'rok') {
            return $this->exportRoczny($date, $format);
        }

        return abort(404);
    }

    private function exportTygodniowy(Carbon $date, $format)
    {
        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfWeek();

        $dniTygodnia = collect();
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $dniTygodnia->push($d->copy());
        }

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]);

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id'));
        }

         $dane = $query->select(
        'produkty.id as produkt_id',
        'produkty.tw_nazwa',
        DB::raw('MIN(ean_codes.kod_ean) as ean'),
        DB::raw('CAST(zamowienia.data_zamowienia AS DATE) as dzien'),
        DB::raw('zamowienia.automat_id'),
        DB::raw('SUM(produkt_zamowienie.ilosc) as ilosc')
        )
        ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('CAST(zamowienia.data_zamowienia AS DATE)'), 'zamowienia.automat_id')
        ->get();

        $produkty = $dane->groupBy('produkt_id');
        $filename = 'tydzien_macierzy_' . $start->format('Y_m_d') . '.' . $format;

        if ($format === 'csv') {
            $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"$filename\"",
                ];

            return response()->stream(function () use ($produkty, $dniTygodnia) {
                $handle = fopen('php://output', 'w');
                // Nagłówki
                $header = ['Produkt', 'EAN'];
                foreach ($dniTygodnia as $dzien) {
                    $header[] = $dzien->format('d.m');
                }
                $header[] = 'Suma';
                fputcsv($handle, $header);

                // Dane
                foreach ($produkty as $produktGroup) {
                    $row = [];
                    $produkt = $produktGroup->first();
                    $row[] = $produkt->tw_nazwa;
                    $row[] = $produkt->ean;
                    $sumaProduktu = 0;
                    foreach ($dniTygodnia as $dzien) {
                        $ilosc = $produktGroup->firstWhere('dzien', $dzien->format('Y-m-d'))->ilosc ?? 0;
                        $row[] = $ilosc;
                        $sumaProduktu += $ilosc;
                    }
                    $row[] = $sumaProduktu;
                    fputcsv($handle, $row);
                }

                // Suma dnia
               $row = ['', 'Suma dnia'];
                foreach ($dniTygodnia as $dzien) {
                    $sumaDnia = $produkty->flatMap(fn($p) => $p)
                        ->filter(function($rek) use ($dzien) {
                            $match = $rek->dzien === $dzien->format('Y-m-d');
                            if (request()->filled('automat_id')) {
                                $match = $match && $rek->automat_id == request('automat_id');
                            }
                            return $match;
                        })
                        ->sum('ilosc');
                    $row[] = $sumaDnia;
                }
                fputcsv($handle, $row);

                fclose($handle);
            }, 200, $headers);
        }

        // XLSX
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $col = 1;

        // Nagłówki
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Produkt');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'EAN');
        foreach ($dniTygodnia as $dzien) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $dzien->format('d.m'));
        }
        $sheet->setCellValueByColumnAndRow($col, $row, 'Suma');
        $headerRow = $row;
        $row++;

        $firstDataRow = $row;
        foreach ($produkty as $produktGroup) {
            $col = 1;
            $produkt = $produktGroup->first();
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->tw_nazwa);
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->ean);
            $sumaProduktu = 0;
            foreach ($dniTygodnia as $dzien) {
                $ilosc = $produktGroup->firstWhere('dzien', $dzien->format('Y-m-d'))->ilosc ?? 0;
                $sheet->setCellValueByColumnAndRow($col++, $row, $ilosc);
                $sumaProduktu += $ilosc;
            }
            $sheet->setCellValueByColumnAndRow($col, $row, $sumaProduktu);
            $row++;
        }
        $lastDataRow = $row - 1;

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, '');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Suma dnia');
        $sumRow = $row; 
        foreach ($dniTygodnia as $dzien) {
            $sumaDnia = $produkty->flatMap(fn($p) => $p)
                ->filter(function($rek) use ($dzien) {
                    $match = $rek->dzien === $dzien->format('Y-m-d');
                    if (request()->filled('automat_id')) {
                        $match = $match && $rek->automat_id == request('automat_id');
                    }
                    return $match;
                })
                ->sum('ilosc');
            $sheet->setCellValueByColumnAndRow($col++, $row, $sumaDnia);
        }

      $lastCol = $sheet->getHighestColumn();
        $this->applyXlsxStyles($sheet, $headerRow, $firstDataRow, $lastDataRow, $lastCol);

        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $eanCol = 'B';
        $firstRow = $headerRow + 1;
        $lastRow = $sumRow; 
        $sheet->getStyle("{$eanCol}{$firstRow}:{$eanCol}{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }


    
    private function exportDzienny(Carbon $date, $format)
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]);

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id'));
        }

        $dane = $query->select(
        'produkty.id as produkt_id',
        'produkty.tw_nazwa',
        DB::raw('MIN(ean_codes.kod_ean) as ean'),
        DB::raw('zamowienia.automat_id'),
        DB::raw('SUM(produkt_zamowienie.ilosc) as ilosc')
        )
        ->groupBy('produkty.id', 'produkty.tw_nazwa', 'zamowienia.automat_id')
        ->get();

        $filename = 'dzien_macierzy_' . $start->format('Y_m_d') . '.' . $format;

       if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->stream(function () use ($dane) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Produkt', 'EAN', 'Ilość']);
                foreach ($dane as $produkt) {
                    if (request()->filled('automat_id') && $produkt->automat_id != request('automat_id')) continue;
                    fputcsv($handle, [$produkt->tw_nazwa, $produkt->ean, $produkt->ilosc]);
                }
                fclose($handle);
            }, 200, $headers);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Produkt');
        $sheet->setCellValue('B1', 'EAN');
        $sheet->setCellValue('C1', 'Ilość');
        $headerRow = 1;
        $firstDataRow = 2;
        $row = 2;
        foreach ($dane as $produkt) {
            $sheet->setCellValue('A' . $row, $produkt->tw_nazwa);
            $sheet->setCellValue('B' . $row, $produkt->ean);
            $sheet->setCellValue('C' . $row, $produkt->ilosc);
            $row++;
        }
        $lastDataRow = $row - 1;
        $sumRow = $row; 

        $lastCol = $sheet->getHighestColumn();
        $this->applyXlsxStyles($sheet, $headerRow, $firstDataRow, $lastDataRow, $lastCol);

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportMiesieczny(Carbon $date, $format)
    {
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $dniMiesiaca = collect();
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $dniMiesiaca->push($d->copy());
        }

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]);

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id'));
        }

        $dane = $query->select(
        'produkty.id as produkt_id',
        'produkty.tw_nazwa',
        DB::raw('MIN(ean_codes.kod_ean) as ean'),
        DB::raw('CAST(zamowienia.data_zamowienia AS DATE) as dzien'),
        DB::raw('zamowienia.automat_id'),
        DB::raw('SUM(produkt_zamowienie.ilosc) as ilosc')
        )
        ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('CAST(zamowienia.data_zamowienia AS DATE)'), 'zamowienia.automat_id')
        ->get();

        $produkty = $dane->groupBy('produkt_id');
        $filename = 'miesiac_macierzy_' . $start->format('Y_m') . '.' . $format;

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->stream(function () use ($produkty, $dniMiesiaca) {
                $handle = fopen('php://output', 'w');
                $header = ['Produkt', 'EAN'];
                foreach ($dniMiesiaca as $dzien) {
                    $header[] = $dzien->format('d.m');
                }
                $header[] = 'Suma';
                fputcsv($handle, $header);

                foreach ($produkty as $produktGroup) {
                    $row = [];
                    $produkt = $produktGroup->first();
                    $row[] = $produkt->tw_nazwa;
                    $row[] = $produkt->ean;
                    $sumaProduktu = 0;
                    foreach ($dniMiesiaca as $dzien) {
                        $ilosc = $produktGroup->firstWhere('dzien', $dzien->format('Y-m-d'))->ilosc ?? 0;
                        $row[] = $ilosc;
                        $sumaProduktu += $ilosc;
                    }
                    $row[] = $sumaProduktu;
                    fputcsv($handle, $row);
                }

                // Suma dnia
               $row = ['', 'Suma dnia'];
                foreach ($dniMiesiaca as $dzien) {
                    $sumaDnia = $produkty->flatMap(fn($p) => $p)
                        ->filter(function($rek) use ($dzien) {
                            $match = $rek->dzien === $dzien->format('Y-m-d');
                            if (request()->filled('automat_id')) {
                                $match = $match && $rek->automat_id == request('automat_id');
                            }
                            return $match;
                        })
                        ->sum('ilosc');
                    $row[] = $sumaDnia;
                }
                fputcsv($handle, $row);

                fclose($handle);
            }, 200, $headers);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Produkt');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'EAN');
        foreach ($dniMiesiaca as $dzien) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $dzien->format('d.m'));
        }
        $sheet->setCellValueByColumnAndRow($col, $row, 'Suma');
        $headerRow = $row;
        $row++;

        $firstDataRow = $row;
        foreach ($produkty as $produktGroup) {
            $col = 1;
            $produkt = $produktGroup->first();
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->tw_nazwa);
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->ean);
            $sumaProduktu = 0;
            foreach ($dniMiesiaca as $dzien) {
                $ilosc = $produktGroup->firstWhere('dzien', $dzien->format('Y-m-d'))->ilosc ?? 0;
                $sheet->setCellValueByColumnAndRow($col++, $row, $ilosc);
                $sumaProduktu += $ilosc;
            }
            $sheet->setCellValueByColumnAndRow($col, $row, $sumaProduktu);
            $row++;
        }
        $lastDataRow = $row - 1;

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, '');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Suma dnia');
        foreach ($dniMiesiaca as $dzien) {
            $sumaDnia = $produkty->flatMap(fn($p) => $p)
                ->filter(function($rek) use ($dzien) {
                    $match = $rek->dzien === $dzien->format('Y-m-d');
                    if (request()->filled('automat_id')) {
                        $match = $match && $rek->automat_id == request('automat_id');
                    }
                    return $match;
                })
                ->sum('ilosc');
            $sheet->setCellValueByColumnAndRow($col++, $row, $sumaDnia);
        }
        $sumRow = $row;

        $lastCol = $sheet->getHighestColumn();
        $this->applyXlsxStyles($sheet, $headerRow, $firstDataRow, $lastDataRow, $lastCol);

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $sheet->getStyle("B{$firstDataRow}:B{$sumRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportRoczny(Carbon $date, $format)
    {
        $start = $date->copy()->startOfYear();
        $end = $date->copy()->endOfYear();

        $miesiace = collect();
        for ($m = $start->copy(); $m <= $end; $m->addMonth()) {
            $miesiace->push($m->copy());
        }

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]);

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id'));
        }

        $dane = $query->select(
        'produkty.id as produkt_id',
        'produkty.tw_nazwa',
        DB::raw('MIN(ean_codes.kod_ean) as ean'),
        DB::raw('MONTH(zamowienia.data_zamowienia) as miesiac'),
        DB::raw('zamowienia.automat_id'),
        DB::raw('SUM(produkt_zamowienie.ilosc) as ilosc')
        )
        ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('MONTH(zamowienia.data_zamowienia)'), 'zamowienia.automat_id')
        ->get();

        $produkty = $dane->groupBy('produkt_id');
        $filename = 'rok_macierzy_' . $start->format('Y') . '.' . $format;

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->stream(function () use ($produkty, $miesiace) {
                $handle = fopen('php://output', 'w');
                $header = ['Produkt', 'EAN'];
                foreach ($miesiace as $miesiac) {
                    $header[] = $miesiac->format('m.Y');
                }
                $header[] = 'Suma';
                fputcsv($handle, $header);

                foreach ($produkty as $produktGroup) {
                    $row = [];
                    $produkt = $produktGroup->first();
                    $row[] = $produkt->tw_nazwa;
                    $row[] = $produkt->ean;
                    $sumaProduktu = 0;
                    foreach ($miesiace as $miesiac) {
                        $ilosc = $produktGroup->firstWhere('miesiac', $miesiac->format('n'))->ilosc ?? 0;
                        $row[] = $ilosc;
                        $sumaProduktu += $ilosc;
                    }
                    $row[] = $sumaProduktu;
                    fputcsv($handle, $row);
                }

                // Suma miesiąca
                $row = ['', 'Suma miesiąca'];
                foreach ($miesiace as $miesiac) {
                    $sumaMiesiaca = $produkty->flatMap(fn($p) => $p)
                        ->filter(function($rek) use ($miesiac) {
                            $match = $rek->miesiac == $miesiac->format('n');
                            if (request()->filled('automat_id')) {
                                $match = $match && $rek->automat_id == request('automat_id');
                            }
                            return $match;
                        })
                        ->sum('ilosc');
                    $row[] = $sumaMiesiaca;
                }
                fputcsv($handle, $row);

                fclose($handle);
            }, 200, $headers);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Produkt');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'EAN');
        foreach ($miesiace as $miesiac) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $miesiac->format('m.Y'));
        }
        $sheet->setCellValueByColumnAndRow($col, $row, 'Suma');
        $headerRow = $row;
        $row++;

        $firstDataRow = $row;
        foreach ($produkty as $produktGroup) {
            $col = 1;
            $produkt = $produktGroup->first();
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->tw_nazwa);
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->ean);
            $sumaProduktu = 0;
            foreach ($miesiace as $miesiac) {
                $ilosc = $produktGroup->firstWhere('miesiac', $miesiac->format('n'))->ilosc ?? 0;
                $sheet->setCellValueByColumnAndRow($col++, $row, $ilosc);
                $sumaProduktu += $ilosc;
            }
            $sheet->setCellValueByColumnAndRow($col, $row, $sumaProduktu);
            $row++;
        }
        $lastDataRow = $row - 1;

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, '');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Suma miesiąca');
        foreach ($miesiace as $miesiac) {
            $sumaMiesiaca = $produkty->flatMap(fn($p) => $p)
                ->filter(function($rek) use ($miesiac) {
                    $match = $rek->miesiac == $miesiac->format('n');
                    if (request()->filled('automat_id')) {
                        $match = $match && $rek->automat_id == request('automat_id');
                    }
                    return $match;
                })
                ->sum('ilosc');
            $sheet->setCellValueByColumnAndRow($col++, $row, $sumaMiesiaca);
        }
        $sumRow = $row;

        $lastCol = $sheet->getHighestColumn();
        $this->applyXlsxStyles($sheet, $headerRow, $firstDataRow, $lastDataRow, $lastCol);

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $sheet->getStyle("B{$firstDataRow}:B{$sumRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }


  private function applyXlsxStyles($sheet, $headerRow, $firstDataRow, $lastDataRow, $lastCol)
    {
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'B91C1C'],
            ],
        ];

        $rowStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '065F46'],
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF']],
        ];

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '222222'],
                ],
            ],
        ];

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray($headerStyle);

        if ($lastDataRow >= $firstDataRow) {
            $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$lastDataRow}")->applyFromArray($rowStyle);
        }

        $sumRow = $lastDataRow + 1;
        $sheet->getStyle("A{$sumRow}:{$lastCol}{$sumRow}")->applyFromArray($rowStyle);

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$sumRow}")->applyFromArray($borderStyle);
    }


     public function exportStraty($zakres, $date = null, $format = 'xlsx')
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        if ($zakres === 'tydzien') {
            return $this->exportStratyTygodniowe($date, $format);
        }
        if ($zakres === 'dzien') {
            return $this->exportStratyDzienne($date, $format);
        }
        if ($zakres === 'miesiac') {
            return $this->exportStratyMiesieczne($date, $format);
        }
        if ($zakres === 'rok') {
            return $this->exportStratyRoczne($date, $format);
        }

        return abort(404);
    }

    private function exportStratyTygodniowe(Carbon $date, $format)
    {
        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfWeek();

        $dniTygodnia = collect();
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $dniTygodnia->push($d->copy());
        }

        $dane = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select(
                'produkty.id as produkt_id',
                'produkty.tw_nazwa',
                DB::raw('MIN(ean_codes.kod_ean) as ean'),
                DB::raw('CAST(straty.data_straty AS DATE) as dzien'),
                DB::raw('SUM(produkt_strata.ilosc) as ilosc')
            )
            ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('CAST(straty.data_straty AS DATE)'))
            ->get();

        $produkty = $dane->groupBy('produkt_id');
        $filename = 'straty_tydzien_' . $start->format('Y_m_d') . '.' . $format;

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->stream(function () use ($produkty, $dniTygodnia) {
                $handle = fopen('php://output', 'w');
                // Nagłówki
                $header = ['Produkt', 'EAN'];
                foreach ($dniTygodnia as $dzien) {
                    $header[] = $dzien->format('d.m');
                }
                $header[] = 'Suma';
                fputcsv($handle, $header);

                // Dane
                foreach ($produkty as $produktGroup) {
                    $row = [];
                    $produkt = $produktGroup->first();
                    $row[] = $produkt->tw_nazwa;
                    $row[] = $produkt->ean;
                    $sumaProduktu = 0;
                    foreach ($dniTygodnia as $dzien) {
                        $ilosc = $produktGroup->firstWhere('dzien', $dzien->format('Y-m-d'))->ilosc ?? 0;
                        $row[] = $ilosc;
                        $sumaProduktu += $ilosc;
                    }
                    $row[] = $sumaProduktu;
                    fputcsv($handle, $row);
                }

                // Suma dnia
                $row = ['', 'Suma dnia'];
                foreach ($dniTygodnia as $dzien) {
                    $sumaDnia = $produkty->flatMap(fn($p) => $p)
                        ->filter(fn($rek) => $rek->dzien === $dzien->format('Y-m-d'))
                        ->sum('ilosc');
                    $row[] = $sumaDnia;
                }
                fputcsv($handle, $row);

                fclose($handle);
            }, 200, $headers);
        }

        // XLSX
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $col = 1;

        // Nagłówki
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Produkt');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'EAN');
        foreach ($dniTygodnia as $dzien) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $dzien->format('d.m'));
        }
        $sheet->setCellValueByColumnAndRow($col, $row, 'Suma');
        $headerRow = $row;
        $row++;

        $firstDataRow = $row;
        foreach ($produkty as $produktGroup) {
            $col = 1;
            $produkt = $produktGroup->first();
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->tw_nazwa);
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->ean);
            $sumaProduktu = 0;
            foreach ($dniTygodnia as $dzien) {
                $ilosc = $produktGroup->firstWhere('dzien', $dzien->format('Y-m-d'))->ilosc ?? 0;
                $sheet->setCellValueByColumnAndRow($col++, $row, $ilosc);
                $sumaProduktu += $ilosc;
            }
            $sheet->setCellValueByColumnAndRow($col, $row, $sumaProduktu);
            $row++;
        }
        $lastDataRow = $row - 1;

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, '');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Suma dnia');
        $sumRow = $row; 
        foreach ($dniTygodnia as $dzien) {
            $sumaDnia = $produkty->flatMap(fn($p) => $p)
                ->filter(fn($rek) => $rek->dzien === $dzien->format('Y-m-d'))
                ->sum('ilosc');
            $sheet->setCellValueByColumnAndRow($col++, $row, $sumaDnia);
        }

        $lastCol = $sheet->getHighestColumn();
        $this->applyXlsxStyles($sheet, $headerRow, $firstDataRow, $lastDataRow, $lastCol);

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $eanCol = 'B';
        $firstRow = $headerRow + 1;
        $lastRow = $sumRow; 
        $sheet->getStyle("{$eanCol}{$firstRow}:{$eanCol}{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportStratyDzienne(Carbon $date, $format)
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $dane = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select(
                'produkty.id as produkt_id',
                'produkty.tw_nazwa',
                DB::raw('MIN(ean_codes.kod_ean) as ean'),
                DB::raw('SUM(produkt_strata.ilosc) as ilosc')
            )
            ->groupBy('produkty.id', 'produkty.tw_nazwa')
            ->get();

        $filename = 'straty_dzien_' . $start->format('Y_m_d') . '.' . $format;

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->stream(function () use ($dane) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Produkt', 'EAN', 'Ilość']);
                foreach ($dane as $produkt) {
                    fputcsv($handle, [$produkt->tw_nazwa, $produkt->ean, $produkt->ilosc]);
                }
                fclose($handle);
            }, 200, $headers);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Produkt');
        $sheet->setCellValue('B1', 'EAN');
        $sheet->setCellValue('C1', 'Ilość');
        $headerRow = 1;
        $firstDataRow = 2;
        $row = 2;
        foreach ($dane as $produkt) {
            $sheet->setCellValue('A' . $row, $produkt->tw_nazwa);
            $sheet->setCellValue('B' . $row, $produkt->ean);
            $sheet->setCellValue('C' . $row, $produkt->ilosc);
            $row++;
        }
        $lastDataRow = $row - 1;
        $sumRow = $row; 

        $lastCol = $sheet->getHighestColumn();
        $this->applyXlsxStyles($sheet, $headerRow, $firstDataRow, $lastDataRow, $lastCol);

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportStratyMiesieczne(Carbon $date, $format)
    {
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $dniMiesiaca = collect();
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $dniMiesiaca->push($d->copy());
        }

        $dane = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select(
                'produkty.id as produkt_id',
                'produkty.tw_nazwa',
                DB::raw('MIN(ean_codes.kod_ean) as ean'),
                DB::raw('CAST(straty.data_straty AS DATE) as dzien'),
                DB::raw('SUM(produkt_strata.ilosc) as ilosc')
            )
            ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('CAST(straty.data_straty AS DATE)'))
            ->get();

        $produkty = $dane->groupBy('produkt_id');
        $filename = 'straty_miesiac_' . $start->format('Y_m') . '.' . $format;

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->stream(function () use ($produkty, $dniMiesiaca) {
                $handle = fopen('php://output', 'w');
                $header = ['Produkt', 'EAN'];
                foreach ($dniMiesiaca as $dzien) {
                    $header[] = $dzien->format('d.m');
                }
                $header[] = 'Suma';
                fputcsv($handle, $header);

                foreach ($produkty as $produktGroup) {
                    $row = [];
                    $produkt = $produktGroup->first();
                    $row[] = $produkt->tw_nazwa;
                    $row[] = $produkt->ean;
                    $sumaProduktu = 0;
                    foreach ($dniMiesiaca as $dzien) {
                        $ilosc = $produktGroup->firstWhere('dzien', $dzien->format('Y-m-d'))->ilosc ?? 0;
                        $row[] = $ilosc;
                        $sumaProduktu += $ilosc;
                    }
                    $row[] = $sumaProduktu;
                    fputcsv($handle, $row);
                }

                // Suma dnia
                $row = ['', 'Suma dnia'];
                foreach ($dniMiesiaca as $dzien) {
                    $sumaDnia = $produkty->flatMap(fn($p) => $p)
                        ->filter(fn($rek) => $rek->dzien === $dzien->format('Y-m-d'))
                        ->sum('ilosc');
                    $row[] = $sumaDnia;
                }
                fputcsv($handle, $row);

                fclose($handle);
            }, 200, $headers);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Produkt');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'EAN');
        foreach ($dniMiesiaca as $dzien) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $dzien->format('d.m'));
        }
        $sheet->setCellValueByColumnAndRow($col, $row, 'Suma');
        $headerRow = $row;
        $row++;

        $firstDataRow = $row;
        foreach ($produkty as $produktGroup) {
            $col = 1;
            $produkt = $produktGroup->first();
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->tw_nazwa);
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->ean);
            $sumaProduktu = 0;
            foreach ($dniMiesiaca as $dzien) {
                $ilosc = $produktGroup->firstWhere('dzien', $dzien->format('Y-m-d'))->ilosc ?? 0;
                $sheet->setCellValueByColumnAndRow($col++, $row, $ilosc);
                $sumaProduktu += $ilosc;
            }
            $sheet->setCellValueByColumnAndRow($col, $row, $sumaProduktu);
            $row++;
        }
        $lastDataRow = $row - 1;

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, '');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Suma dnia');
        foreach ($dniMiesiaca as $dzien) {
            $sumaDnia = $produkty->flatMap(fn($p) => $p)
                ->filter(fn($rek) => $rek->dzien === $dzien->format('Y-m-d'))
                ->sum('ilosc');
            $sheet->setCellValueByColumnAndRow($col++, $row, $sumaDnia);
        }
        $sumRow = $row;

        $lastCol = $sheet->getHighestColumn();
        $this->applyXlsxStyles($sheet, $headerRow, $firstDataRow, $lastDataRow, $lastCol);

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $sheet->getStyle("B{$firstDataRow}:B{$sumRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportStratyRoczne(Carbon $date, $format)
    {
        $start = $date->copy()->startOfYear();
        $end = $date->copy()->endOfYear();

        $miesiace = collect();
        for ($m = $start->copy(); $m <= $end; $m->addMonth()) {
            $miesiace->push($m->copy());
        }

        $dane = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select(
                'produkty.id as produkt_id',
                'produkty.tw_nazwa',
                DB::raw('MIN(ean_codes.kod_ean) as ean'),
                DB::raw('MONTH(straty.data_straty) as miesiac'),
                DB::raw('SUM(produkt_strata.ilosc) as ilosc')
            )
            ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('MONTH(straty.data_straty)'))
            ->get();

        $produkty = $dane->groupBy('produkt_id');
        $filename = 'straty_rok_' . $start->format('Y') . '.' . $format;

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->stream(function () use ($produkty, $miesiace) {
                $handle = fopen('php://output', 'w');
                $header = ['Produkt', 'EAN'];
                foreach ($miesiace as $miesiac) {
                    $header[] = $miesiac->format('m.Y');
                }
                $header[] = 'Suma';
                fputcsv($handle, $header);

                foreach ($produkty as $produktGroup) {
                    $row = [];
                    $produkt = $produktGroup->first();
                    $row[] = $produkt->tw_nazwa;
                    $row[] = $produkt->ean;
                    $sumaProduktu = 0;
                    foreach ($miesiace as $miesiac) {
                        $ilosc = $produktGroup->firstWhere('miesiac', $miesiac->format('n'))->ilosc ?? 0;
                        $row[] = $ilosc;
                        $sumaProduktu += $ilosc;
                    }
                    $row[] = $sumaProduktu;
                    fputcsv($handle, $row);
                }

                // Suma miesiąca
                $row = ['', 'Suma miesiąca'];
                foreach ($miesiace as $miesiac) {
                    $sumaMiesiaca = $produkty->flatMap(fn($p) => $p)
                        ->filter(fn($rek) => $rek->miesiac == $miesiac->format('n'))
                        ->sum('ilosc');
                    $row[] = $sumaMiesiaca;
                }
                fputcsv($handle, $row);

                fclose($handle);
            }, 200, $headers);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Produkt');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'EAN');
        foreach ($miesiace as $miesiac) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $miesiac->format('m.Y'));
        }
        $sheet->setCellValueByColumnAndRow($col, $row, 'Suma');
        $headerRow = $row;
        $row++;

        $firstDataRow = $row;
        foreach ($produkty as $produktGroup) {
            $col = 1;
            $produkt = $produktGroup->first();
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->tw_nazwa);
            $sheet->setCellValueByColumnAndRow($col++, $row, $produkt->ean);
            $sumaProduktu = 0;
            foreach ($miesiace as $miesiac) {
                $ilosc = $produktGroup->firstWhere('miesiac', $miesiac->format('n'))->ilosc ?? 0;
                $sheet->setCellValueByColumnAndRow($col++, $row, $ilosc);
                $sumaProduktu += $ilosc;
            }
            $sheet->setCellValueByColumnAndRow($col, $row, $sumaProduktu);
            $row++;
        }
        $lastDataRow = $row - 1;

        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, '');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Suma miesiąca');
        foreach ($miesiace as $miesiac) {
            $sumaMiesiaca = $produkty->flatMap(fn($p) => $p)
                ->filter(fn($rek) => $rek->miesiac == $miesiac->format('n'))
                ->sum('ilosc');
            $sheet->setCellValueByColumnAndRow($col++, $row, $sumaMiesiaca);
        }
        $sumRow = $row;

        $lastCol = $sheet->getHighestColumn();
        $this->applyXlsxStyles($sheet, $headerRow, $firstDataRow, $lastDataRow, $lastCol);

        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $sheet->getStyle("B{$firstDataRow}:B{$sumRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }


}
