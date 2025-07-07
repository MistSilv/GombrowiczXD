<?php

namespace App\Http\Controllers;

use App\Models\Produkt;
use App\Models\Zamowienie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ZamowienieMail;
use App\Exports\ZamowienieExport;
use Maatwebsite\Excel\Facades\Excel;

class ProduktController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Produkt $produkt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produkt $produkt)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produkt $produkt)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produkt $produkt)
    {
        //
    }

    private function buildDeficyty(): \Illuminate\Support\Collection
    {
        // produkty obce
        $produkty = Produkt::where('is_wlasny', false)->get();

        // SUMA wsadów
        $wsady = DB::table('produkt_wsad')
            ->join('produkty','produkt_wsad.produkt_id','=','produkty.id')
            ->where('produkty.is_wlasny',false)
            ->select('produkt_wsad.produkt_id', DB::raw('SUM(ilosc) as suma'))
            ->groupBy('produkt_wsad.produkt_id')
            ->pluck('suma','produkt_id');

        // SUMA zamówień ogólnych (automat_id = null)
        $zam = DB::table('produkt_zamowienie')
            ->join('zamowienia','produkt_zamowienie.zamowienie_id','=','zamowienia.id')
            ->join('produkty',   'produkt_zamowienie.produkt_id',  '=','produkty.id')
            ->whereNull('zamowienia.automat_id')
            ->where('produkty.is_wlasny',false)
            ->select('produkt_zamowienie.produkt_id', DB::raw('SUM(ilosc) as suma'))
            ->groupBy('produkt_zamowienie.produkt_id')
            ->pluck('suma','produkt_id');

        // map → kolekcja z nazwą i deficytem
        return $produkty->map(fn($p) => [
            'id'         => $p->id,
            'nazwa'      => $p->tw_nazwa,
            'wsady'      => $wsady[$p->id] ?? 0,
            'zamowienia' => $zam[$p->id]   ?? 0,
            'deficyt'    => ($wsady[$p->id] ?? 0) - ($zam[$p->id] ?? 0),
        ]);
    }

    public function formularzNoweZamowienie()
    {
        return view('produkty.niewlasne_edit_zamowienie', [
            'produkty'    => Produkt::where('is_wlasny', false)->get(),
            'deficyty'    => $this->buildDeficyty(),
            'zamowienieId'=> null,
        ]);
    }

    public function edytujZamowienie($zamowienieId)
    {
        $produkty = DB::table('produkty')
            ->leftJoin('produkt_zamowienie', function($join) use ($zamowienieId) {
                $join->on('produkty.id', '=', 'produkt_zamowienie.produkt_id')
                    ->where('produkt_zamowienie.zamowienie_id', $zamowienieId);
            })
            ->select('produkty.id', 'produkty.tw_nazwa', 'produkt_zamowienie.ilosc')
            ->where('produkty.is_wlasny', false)
            ->get();

        return view('produkty.niewlasne_edit_zamowienie', [
            'produkty' => $produkty,
            'zamowienieId' => $zamowienieId
        ]);
    }

    public function zapiszZamowienie(Request $request)
    {
        $zamowienieId = $request->input('zamowienieId');
        $ilosci = $request->input('ilosci', []);
        $wyslijEmail = $request->input('wyslij_email', false);

        if (!$zamowienieId) {
            $zamowienieId = DB::table('zamowienia')->insertGetId([
                'data_zamowienia' => now(),
                'data_realizacji' => null,
                'automat_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($ilosci as $produktId => $ilosc) {
            $istnieje = DB::table('produkt_zamowienie')
                ->where('zamowienie_id', $zamowienieId)
                ->where('produkt_id', $produktId)
                ->exists();

            if ($istnieje) {
                DB::table('produkt_zamowienie')
                    ->where('zamowienie_id', $zamowienieId)
                    ->where('produkt_id', $produktId)
                    ->update(['ilosc' => $ilosc]);
            } else {
                if ($ilosc > 0) {
                    DB::table('produkt_zamowienie')->insert([
                        'zamowienie_id' => $zamowienieId,
                        'produkt_id' => $produktId,
                        'ilosc' => $ilosc
                    ]);
                }
            }
        }

        // Wysyłka emaila jeśli zaznaczono
        if ($wyslijEmail) {
            return $this->wyslijEmailZamowienia($zamowienieId);
        }

        return redirect()->route('zamowienia.show', ['zamowienie' => $zamowienieId])
                ->with('success', 'Ilości zostały zapisane.');
    }

    /**
     * Wyślij email z zamówieniem produktów nie-własnych
     */
    public function wyslijEmailZamowienia($zamowienieId)
    {
        // Znajdź zamówienie z produktami i ich kodami EAN
        $zamowienie = Zamowienie::with(['produkty' => function($query) {
            $query->select('produkty.id', 'tw_nazwa')
                  ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
                  ->addSelect('ean_codes.kod_ean as ean');
        }])->findOrFail($zamowienieId);

        // Generuj plik Excel z dodatkową kolumną EAN
        $xlsxContent = Excel::raw(new ZamowienieExport($zamowienie), \Maatwebsite\Excel\Excel::XLSX);

        // Wyślij email
        Mail::to('projekttest100969@gmail.com')->send(new ZamowienieMail($xlsxContent, $zamowienie));

        return redirect()->route('zamowienia.show', ['zamowienie' => $zamowienieId])
            ->with('success', 'Ilości zostały zapisane.')
            ->with('email_sent', 'Email z zamówieniem został wysłany.');
    }

    /**
     * Pobierz plik Excel z zamówieniem (z kodem EAN)
     */
    public function pobierzZamowienieExcel($zamowienieId)
    {
        $zamowienie = Zamowienie::with(['produkty' => function($query) {
            $query->select('produkty.id', 'tw_nazwa')
                  ->leftJoin('ean_codes', 'produkty.id', '=', 'ean_codes.produkt_id')
                  ->addSelect('ean_codes.kod_ean as ean');
        }])->findOrFail($zamowienieId);
        
        $date = now()->format('Y-m-d');

        return Excel::download(
            new ZamowienieExport($zamowienie), 
            "zamowienie_{$zamowienieId}_{$date}.xlsx"
        );
    }
}