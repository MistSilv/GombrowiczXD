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

    public function niewlasne()
    {
        // Pobieramy produkty niewłasne z sumą ilości z wsadów
        $produkty = DB::table('produkty')
            ->leftJoin('produkt_wsad', 'produkty.id', '=', 'produkt_wsad.produkt_id')
            ->leftJoin('wsady', 'produkt_wsad.wsad_id', '=', 'wsady.id')
            ->select('produkty.tw_nazwa', DB::raw('COALESCE(SUM(produkt_wsad.ilosc), 0) as suma_ilosci'))
            ->where('produkty.is_wlasny', false)
            ->groupBy('produkty.id', 'produkty.tw_nazwa')
            ->get();

        return view('produkty.niewlasne', compact('produkty'));
    }

   
}
