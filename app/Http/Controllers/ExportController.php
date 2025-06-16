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
        elseif ($zakres === 'miesiac') {
            
            return abort(404); // Tymczasowo, do implementacji
        }
        elseif ($zakres === 'rok') {
            
            
            return abort(404); // Tymczasowo, do implementacji
        }
        elseif ($zakres === 'dzien') {
            
            return abort(404); // Tymczasowo, do implementacji
        }
        else{
            
            return abort(404); // Nieznany zakres
        }
    }

    private function exportTygodniowy(Carbon $date, $format)
    {
        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfWeek();

        // Pobierz listę dni tygodnia
        $dniTygodnia = collect();
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $dniTygodnia->push($d->copy());
        }

        // Dane: produkt + dzień => suma ilości
        $dane = DB::table('produkt_zamowienie')
        ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
        ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
        ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id') // dołączamy EAN
        ->whereBetween('zamowienia.data_zamowienia', [$start, $end])
        ->select(
            'produkty.id as produkt_id',
            'produkty.tw_nazwa',
            DB::raw('MIN(ean_codes.kod_ean) as ean'), // wybieramy pierwszy EAN (min), bo może być wiele
            DB::raw('CAST(zamowienia.data_zamowienia AS DATE) as dzien'),
            DB::raw('SUM(produkt_zamowienie.ilosc) as ilosc')
        )
        ->groupBy('produkty.id', 'produkty.tw_nazwa', DB::raw('CAST(zamowienia.data_zamowienia AS DATE)'))
        ->get();


        // Grupuj dane wg produktu
        $produkty = $dane->groupBy('produkt_id');

        // Stwórz arkusz
        $spreadsheet = new Spreadsheet();
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

        $row++;

        // Dane per produkt
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

        // Wiersz sumy wszystkich produktów per dzień
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col++, $row, '');
        $sheet->setCellValueByColumnAndRow($col++, $row, 'Suma dnia');

        foreach ($dniTygodnia as $i => $dzien) {
            $sumaDnia = $produkty->flatMap(fn($p) => $p)
                ->filter(fn($rek) => $rek->dzien === $dzien->format('Y-m-d'))
                ->sum('ilosc');

            $sheet->setCellValueByColumnAndRow($col++, $row, $sumaDnia);
        }

        // Nie wypełniamy "sumy sum" na końcu — opcjonalnie można

        // Nazwa pliku
        $filename = 'tydzien_macierzy_' . $start->format('Y_m_d') . '.xlsx';

        // Export
        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

}
