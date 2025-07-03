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

    public function noweZamowienie()
    {
        // Tworzenie zamówienia
        $zamowienieId = DB::table('zamowienia')->insertGetId([
            'data_zamowienia' => now(),
            'data_realizacji' => null,
            'automat_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Przekierowanie do edycji produktów
        return redirect()->route('produkty.zamowienie.edytuj', ['zamowienieId' => $zamowienieId]);
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

    public function zapiszZamowienie(Request $request, $zamowienieId)
    {
        $ilosci = $request->input('ilosci', []);

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

        return redirect()->route('produkty.zamowienie.edytuj', ['zamowienieId' => $zamowienieId])
                        ->with('success', 'Ilości zostały zapisane.');
    }


   
}
