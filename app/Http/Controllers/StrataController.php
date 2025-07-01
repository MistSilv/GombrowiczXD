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
        $straty = Strata::with('automat')->orderByDesc('data_straty')->paginate(20); // Pobierz straty z bazy danych, posortowane malejąco według daty straty i paginuj wyniki

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
            'produkty.*.ilosc' => 'required|integer|min:1|max:2147483647', // Poprawiony zakres ilości
        ]); // Walidacja danych wejściowych

        $strata = Strata::create([
            'automat_id' => $request->input('automat_id'),
            'data_straty' => $request->input('data_straty'),
            'opis' => $request->input('opis'),
        ]); // Tworzenie nowej straty

        foreach ($request->produkty as $pozycja) {
            $strata->produkty()->attach($pozycja['produkt_id'], ['ilosc' => $pozycja['ilosc']]); // Dodawanie produktów do straty z ilością
        }

        return redirect()->route('zamowienia.create', ['automat_id' => $request->automat_id])
                     ->with('success', 'Straty zostały zapisane.'); // Przekierowanie do formularza zamówienia z parametrem automat_id
    }

    public function show(Strata $strata)
    {
        $strata->load('produkty', 'automat'); // Ładowanie relacji produktów i automatu dla danej straty
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

    // podsumowania dnia
    public function podsumowanieDnia($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today(); // Ustawienie daty na dzisiaj, jeśli nie podano

        $produkty = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->whereDate('straty.data_straty', $date)
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_strata.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get(); // Pobranie produktów z sumą ilości strat

        return view('straty.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $date->format('Y-m-d'),
            'typ' => 'Dzień',
        ]); // Podsumowanie strat dla danego dnia
    }

    // podsumowania tygodnia
    public function podsumowanieTygodnia($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today(); // Ustawienie daty na dzisiaj, jeśli nie podano
        $start = $date->copy()->startOfWeek(); // Początek tygodnia
        $end = $date->copy()->endOfWeek(); // Koniec tygodnia

        $produkty = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_strata.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get(); // Pobranie produktów z sumą ilości strat w danym tygodniu

        return view('straty.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y-m-d') . ' do ' . $end->format('Y-m-d'),
            'typ' => 'Tydzień',
        ]); // Podsumowanie strat dla danego tygodnia
    }

    // podsumowania miesiąca
    public function podsumowanieMiesiaca($month = null)
    {
        $date = $month ? Carbon::parse($month) : Carbon::today(); // Ustawienie daty na dzisiaj, jeśli nie podano
        $start = $date->copy()->startOfMonth(); // Początek miesiąca
        $end = $date->copy()->endOfMonth(); // Koniec miesiąca

        $produkty = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_strata.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get(); // Pobranie produktów z sumą ilości strat w danym miesiącu

        return view('straty.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y-m'),
            'typ' => 'Miesiąc',
        ]); // Podsumowanie strat dla danego miesiąca
    }

    // podsumowania roku
    public function podsumowanieRoku($year = null)
    {
        $date = $year ? Carbon::parse($year . '-01-01') : Carbon::today(); // Ustawienie daty na pierwszy dzień roku, jeśli nie podano
        $start = $date->copy()->startOfYear(); // Początek roku
        $end = $date->copy()->endOfYear(); // Koniec roku

        $produkty = DB::table('produkt_strata')
            ->join('straty', 'produkt_strata.strata_id', '=', 'straty.id')
            ->join('produkty', 'produkt_strata.produkt_id', '=', 'produkty.id')
            ->whereBetween('straty.data_straty', [$start, $end])
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_strata.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get(); // Pobranie produktów z sumą ilości strat w danym roku

        return view('straty.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y'),
            'typ' => 'Rok',
        ]); // Podsumowanie strat dla danego roku
    }
}
