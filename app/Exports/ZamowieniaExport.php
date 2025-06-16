<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;



class ZamowieniaExport implements FromView
{
    protected $zakres;
    protected $date;

    public function __construct($zakres, $date = null)
    {
        $this->zakres = $zakres;
        $this->date = $date ? Carbon::parse($date) : Carbon::today();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        if ($this->zakres === 'tydzien') {
            $start = $this->date->copy()->startOfWeek();
            $end = $this->date->copy()->endOfWeek();

            // Dane dzienne
            $daneDni = [];
            for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
                $produkty = DB::table('produkt_zamowienie')
                    ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
                    ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
                    ->whereDate('zamowienia.data_zamowienia', $day)
                    ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
                    ->groupBy('produkty.tw_nazwa')
                    ->get();

                $daneDni[$day->format('l d.m.Y')] = $produkty;
            }

            // Podsumowanie całego tygodnia
            $podsumowanie = DB::table('produkt_zamowienie')
                ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
                ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
                ->whereBetween('zamowienia.data_zamowienia', [$start, $end])
                ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
                ->groupBy('produkty.tw_nazwa')
                ->get();

            return view('exports.zamowienia_tydzien', [
                'daneDni' => $daneDni,
                'podsumowanie' => $podsumowanie,
                'okres' => $start->format('d.m.Y') . ' - ' . $end->format('d.m.Y'),
            ]);
        }


    }
}
