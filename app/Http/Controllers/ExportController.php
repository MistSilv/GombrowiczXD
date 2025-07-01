<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class ExportController extends Controller
{
    //główny kontroler eksportu danych
    public function exportZamowienia($zakres, $date = null, $format = 'xlsx')
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        return $this->eksportMacierz(
            'zamowienia',
            $zakres,
            $date,
            $format,
            [$this, 'pobierzDaneZamowien']
        ); // Eksportuje zamówienia w zależności od zakresu i formatu
    }

    //główny kontroler eksportu strat
    public function exportStraty($zakres, $date = null, $format = 'xlsx')
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        return $this->eksportMacierz(
            'straty',
            $zakres,
            $date,
            $format,
            [$this, 'pobierzDaneStrat']
        ); // Eksportuje straty w zależności od zakresu i formatu
    }

    //wszystkie eksporty danych zamówień i strat są realizowane przez tę funkcję
    private function eksportMacierz($typ, $zakres, Carbon $date, $format, callable $daneCallback)
    {
        // Ustal zakres i etykiety kolumn
        if ($zakres === 'rok') {
            $start = $date->copy()->startOfYear();
            $end = $date->copy()->endOfYear();
            $okresy = collect();
            $iter = $start->copy();
            while ($iter <= $end) {
                $okresy->push($iter->copy());
                $iter->addMonth();
            }
            $etykiety = $okresy->map(fn($m) => $m->format('m-Y'));
        } else {
            $start = match ($zakres) {
                'dzien'   => $date->copy()->startOfDay(),
                'tydzien' => $date->copy()->startOfWeek(),
                'miesiac' => $date->copy()->startOfMonth(),
                default   => abort(404),
            };
            $end = match ($zakres) {
                'dzien'   => $date->copy()->endOfDay(),
                'tydzien' => $date->copy()->endOfWeek(),
                'miesiac' => $date->copy()->endOfMonth(),
            };
            $okresy = collect();
            $iter = $start->copy();
            while ($iter <= $end) {
                $okresy->push($iter->copy());
                $iter->addDay();
            }
            $etykiety = $okresy->map(fn($d) => $d->format('d-m'));
        }

        $dane = call_user_func($daneCallback, $start, $end, $zakres);
        $produkty = $dane->groupBy('produkt_id');

        $filename = "{$typ}_{$zakres}_{$start->format('Y_m_d')}.{$format}";

        return $format === 'csv'
            ? $this->generujCsv($produkty, $okresy, $etykiety, $zakres)
            : $this->generujXlsx($produkty, $okresy, $etykiety, $filename, $zakres);
    }

    //pobiera dane zamówień 
    private function pobierzDaneZamowien($start, $end, $zakres)
    {
        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end])
            ->when(request('automat_id'), fn($q) => $q->where('zamowienia.automat_id', request('automat_id')));

        if ($zakres === 'rok') {
            $query->select(
                'produkty.id as produkt_id',
                'produkty.tw_nazwa',
                DB::raw('MIN(ean_codes.kod_ean) as ean'),
                DB::raw('MONTH(zamowienia.data_zamowienia) as miesiac'),
                DB::raw('zamowienia.automat_id'),
                DB::raw('SUM(produkt_zamowienie.ilosc) as ilosc')
            )
            ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('MONTH(zamowienia.data_zamowienia)'), 'zamowienia.automat_id');
        } else {
            $query->select(
                'produkty.id as produkt_id',
                'produkty.tw_nazwa',
                DB::raw('MIN(ean_codes.kod_ean) as ean'),
                DB::raw('CAST(zamowienia.data_zamowienia AS DATE) as dzien'),
                DB::raw('zamowienia.automat_id'),
                DB::raw('SUM(produkt_zamowienie.ilosc) as ilosc')
            )
            ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('CAST(zamowienia.data_zamowienia AS DATE)'), 'zamowienia.automat_id');
        }

        return $query->get();
    }

    //pobiera dane strat
    private function pobierzDaneStrat($start, $end, $zakres)
    {
        $query = DB::table('produkty_straty')
            ->join('straty', 'produkty_straty.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkty_straty.produkt_id', '=', 'produkty.id')
            ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
            ->whereBetween('straty.data_straty', [$start, $end]);

        if ($zakres === 'rok') {
            $query->select(
                'produkty.id as produkt_id',
                'produkty.tw_nazwa',
                DB::raw('MIN(ean_codes.kod_ean) as ean'),
                DB::raw('MONTH(straty.data_straty) as miesiac'),
                DB::raw('SUM(produkty_straty.ilosc) as ilosc')
            )
            ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('MONTH(straty.data_straty)'));
        } else {
            $query->select(
                'produkty.id as produkt_id',
                'produkty.tw_nazwa',
                DB::raw('MIN(ean_codes.kod_ean) as ean'),
                DB::raw('CAST(straty.data_straty AS DATE) as dzien'),
                DB::raw('SUM(produkty_straty.ilosc) as ilosc')
            )
            ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('CAST(straty.data_straty AS DATE)'));
        }

        return $query->get();
    }

    //generuje eksport do CSV
    private function generujCsv($produkty, $okresy, $etykiety, $zakres)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"export.csv\"",
        ];

        return response()->stream(function () use ($produkty, $okresy, $etykiety, $zakres) {
            $handle = fopen('php://output', 'w');

            $naglowek = ['Produkt', 'EAN'];
            foreach ($etykiety as $label) {
                $naglowek[] = $label;
            }
            $naglowek[] = 'Suma';
            fputcsv($handle, $naglowek);

            foreach ($produkty as $grupa) {
                $p = $grupa->first();
                $row = [$p->tw_nazwa, $p->ean];
                $suma = 0;

                foreach ($okresy as $okres) {
                    if ($zakres === 'rok') {
                        $ilosc = $grupa->firstWhere('miesiac', $okres->format('n'))->ilosc ?? 0;
                    } else {
                        $ilosc = $grupa->firstWhere('dzien', $okres->format('Y-m-d'))->ilosc ?? 0;
                    }
                    $row[] = $ilosc;
                    $suma += $ilosc;
                }

                $row[] = $suma;
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, $headers);
    }


//generuje eksport do XLSX
private function generujXlsx($produkty, $okresy, $etykiety, $filename, $zakres)
{
    if (empty($produkty)) {
        throw new \RuntimeException('Brak danych produktów do eksportu');
    }

    if (empty($okresy)) {
        throw new \RuntimeException('Brak zakresu dat do eksportu');
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Nagłówki kolumn
    $header = ['Produkt', 'EAN'];
    foreach ($etykiety as $label) {
        $header[] = $label;
    }
    $header[] = 'Suma';

    $sheet->fromArray($header, null, 'A1');

    // Styl nagłówków
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFD9D9D9']
        ],
        'borders' => [
            'bottom' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM
            ]
        ]
    ]);

    // Wypełnianie danych
    $rowIndex = 2;
    foreach ($produkty as $produktId => $grupa) {
        $p = $grupa->first();

        $row = [$p->tw_nazwa, ''];

        // Formatowanie EAN jako tekst
        if (!empty($p->ean)) {
            $sheet->setCellValueExplicit("B{$rowIndex}", $p->ean, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }

        $suma = 0;
        foreach ($okresy as $okres) {
            if ($zakres === 'rok') {
                $ilosc = $grupa->firstWhere('miesiac', $okres->format('n'))?->ilosc ?? 0;
            } else {
                $ilosc = $grupa->firstWhere('dzien', $okres->format('Y-m-d'))?->ilosc ?? 0;
            }
            $row[] = $ilosc;
            $suma += $ilosc;
        }

        $row[] = $suma;

        $sheet->fromArray($row, null, "A{$rowIndex}");

        $rowIndex++;
    }

    $lastCol = $sheet->getHighestColumn();
    $lastColIndex = Coordinate::columnIndexFromString($lastCol);

    // Sumy dzienne/miesięczne pod tabelą
    $sumRowIndex = $rowIndex;
    $sheet->setCellValue("A{$sumRowIndex}", $zakres === 'rok' ? 'Suma miesiąca' : 'Suma dnia');

    // Sumowanie ilości w kolumnach z okresami
    for ($col = 3; $col < $lastColIndex; $col++) {
        $columnLetter = Coordinate::stringFromColumnIndex($col);
        $sumRange = "{$columnLetter}2:{$columnLetter}" . ($rowIndex - 1);
        $sheet->setCellValue($columnLetter . $sumRowIndex, "=SUM({$sumRange})");
    }

    // Sumowanie sumy końcowej (kolumna suma)
    $sumColLetter = Coordinate::stringFromColumnIndex($lastColIndex);
    $sumRange = "{$sumColLetter}2:{$sumColLetter}" . ($rowIndex - 1);
    $sheet->setCellValue($sumColLetter . $sumRowIndex, "=SUM({$sumRange})");

    // Stylowanie wiersza sum
    $sheet->getStyle("A{$sumRowIndex}:{$lastCol}{$sumRowIndex}")->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFE2EFDA']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000']
            ],
        ],
    ]);

    // Autodopasowanie szerokości kolumn
    for ($col = 1; $col <= $lastColIndex; $col++) {
        $columnLetter = Coordinate::stringFromColumnIndex($col);
        $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
    }

    // Obramowanie wszystkich danych
    $sheet->getStyle("A1:{$lastCol}{$sumRowIndex}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000']
            ],
        ],
    ]);

    // Zablokowanie nagłówków
    $sheet->freezePane('A2');

    $writer = new Xlsx($spreadsheet);

    while (ob_get_level()) {
        ob_end_clean();
    }

    return response()->streamDownload(
        function () use ($writer) {
            $writer->save('php://output');
        },
        $filename,
        [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . rawurlencode($filename) . '"',
        ]
    );
}

//pojedyńcze zamówienie kody
public function exportPojedynczeZamowienie($zamowienieId, $format = 'xlsx')
{
    $zamowienie = DB::table('zamowienia')
        ->join('produkt_zamowienie', 'zamowienia.id', '=', 'produkt_zamowienie.zamowienie_id')
        ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
        ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
        ->select(
            'produkty.tw_nazwa',
            'ean_codes.kod_ean',
            'produkt_zamowienie.ilosc'
        )
        ->where('zamowienia.id', $zamowienieId)
        ->get(); // Pobiera produkty dla pojedynczego zamówienia

    if ($format === 'csv') {
        return $this->generujCsvDlaZamowienia($zamowienie, $zamowienieId); // Eksportuje zamówienie do CSV
    }

    return $this->generujXlsxDlaZamowienia($zamowienie, $zamowienieId); // Eksportuje zamówienie do XLSX
}

//eksport dla pojedynczego zamówienia csv
private function generujCsvDlaZamowienia($produkty, $zamowienieId)
{
    $filename = "zamowienie_{$zamowienieId}.csv";
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ]; // Ustawia nagłówki dla pliku CSV

    return response()->stream(function () use ($produkty) {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Produkt', 'EAN', 'Ilość']); 

        foreach ($produkty as $p) {
            fputcsv($handle, [$p->tw_nazwa, $p->kod_ean, $p->ilosc]);
        }

        fclose($handle);
    }, 200, $headers); // Zwraca odpowiedź z plikiem CSV
}

//eksport dla pojedynczego zamówienia xlsx
private function generujXlsxDlaZamowienia($produkty, $zamowienieId)
{
    $filename = "zamowienie_{$zamowienieId}.xlsx";
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Nagłówki
    $sheet->fromArray(['Produkt', 'EAN', 'Ilość'], null, 'A1');

    // Styl nagłówków
    $sheet->getStyle('A1:C1')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFD9D9D9']
        ],
        'borders' => [
            'bottom' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM
            ]
        ]
    ]);

    // Dane produktów
    $rowIndex = 2;
    $suma = 0;

    foreach ($produkty as $p) {
        $sheet->setCellValue("A{$rowIndex}", $p->tw_nazwa);

        if (!empty($p->kod_ean)) {
            $sheet->setCellValueExplicit("B{$rowIndex}", $p->kod_ean, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }

        $sheet->setCellValue("C{$rowIndex}", $p->ilosc);
        $suma += $p->ilosc;

        $rowIndex++;
    }

    // Podsumowanie ilości
    $sumRow = $rowIndex;
    $sheet->setCellValue("A{$sumRow}", 'Suma:');
    $sheet->setCellValue("C{$sumRow}", "=SUM(C2:C" . ($rowIndex - 1) . ")");

    // Styl wiersza sumy
    $sheet->getStyle("A{$sumRow}:C{$sumRow}")->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFE2EFDA']
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000']
            ],
        ],
    ]);

    // Autodopasowanie szerokości kolumn
    foreach (range('A', 'C') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);

    while (ob_get_level()) {
        ob_end_clean();
    }

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

}


