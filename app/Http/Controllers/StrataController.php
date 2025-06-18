<?php

namespace App\Http\Controllers;

use App\Models\Strata;
use App\Models\Produkt;
use App\Models\Automat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use Carbon\Carbon;


class StrataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $straty = Strata::with('automat')->orderByDesc('data_straty')->paginate(20);

        return view('straty.index', compact('straty'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $produkty = Produkt::orderBy('tw_nazwa')->get();
        $automatId = $request->get('automat_id');
        $automat = $automatId ? Automat::findOrFail($automatId) : null;

        return view('straty.create', compact('produkty', 'automat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'automat_id' => 'required|exists:automats,id',
            'data_straty' => 'required|date',
            'opis' => 'nullable|string',
            'produkty' => 'required|array|min:1',
            'produkty.*.produkt_id' => 'required|exists:produkty,id',
            'produkty.*.ilosc' => 'required|integer|min:1',
        ]);

        $strata = Strata::create([
            'automat_id' => $request->input('automat_id'),
            'data_straty' => $request->input('data_straty'),
            'opis' => $request->input('opis'),
        ]);

        foreach ($request->produkty as $pozycja) {
            $strata->produkty()->attach($pozycja['produkt_id'], ['ilosc' => $pozycja['ilosc']]);
        }

        return redirect()->route('zamowienia.create', ['automat_id' => $request->automat_id])
                     ->with('success', 'Straty zostały zapisane.');
    }

    public function show(Strata $strata)
    {
        $strata->load('produkty', 'automat');
        return view('straty.show', compact('strata'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //3
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function podsumowanieDnia($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        $produkty = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->whereDate('straty.data_straty', $date)
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_strata.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get();

        return view('straty.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $date->format('Y-m-d'),
            'typ' => 'Dzień',
        ]);
    }

    public function podsumowanieTygodnia($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfWeek();

        $produkty = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_strata.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get();

        return view('straty.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y-m-d') . ' do ' . $end->format('Y-m-d'),
            'typ' => 'Tydzień',
        ]);
    }

    public function podsumowanieMiesiaca($month = null)
    {
        $date = $month ? Carbon::parse($month) : Carbon::today();
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $produkty = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_strata.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get();

        return view('straty.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y-m'),
            'typ' => 'Miesiąc',
        ]);
    }

    public function podsumowanieRoku($year = null)
    {
        $date = $year ? Carbon::parse($year . '-01-01') : Carbon::today();
        $start = $date->copy()->startOfYear();
        $end = $date->copy()->endOfYear();

        $produkty = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_strata.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get();

        return view('straty.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y'),
            'typ' => 'Rok',
        ]);
    }
}
