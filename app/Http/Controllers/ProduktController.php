<?php

namespace App\Http\Controllers;

use App\Models\Produkt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'deficyty'    => $this->buildDeficyty(),   // ← jest
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

        return redirect()->route('zamowienia.show', ['zamowienie' => $zamowienieId])
                ->with('success', 'Ilości zostały zapisane.');

    }



   
}
