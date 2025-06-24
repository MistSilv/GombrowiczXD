<?php

namespace App\Http\Controllers;

use App\Models\Zamowienie;
use App\Models\Produkt;
use App\Models\Automat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use Carbon\Carbon;



class ZamowienieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $dzisiaj = Carbon::today()->toDateString();
        $query = Zamowienie::where(function($q) use ($dzisiaj) {
            $q->whereNull('data_realizacji')
            ->orWhere('data_realizacji', '>=', $dzisiaj);
        });

        if ($request->has('automat_id')) {
            $query->where('automat_id', $request->automat_id);
        }

        $zamowienia = $query->orderByDesc('data_zamowienia')->get();

        return view('zamowienia.index', compact('zamowienia'));
    }


    /**
     * Show the form for creating a new resource.
     */


   public function create(Request $request)
    {
        $produkty = Produkt::orderBy('tw_nazwa')->get();
        $automatId = $request->get('automat_id');
        $automat = $automatId ? Automat::findOrFail($automatId) : null;

        return view('zamowienia.create', compact('produkty', 'automat'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produkty' => 'required|array|min:1',
            'produkty.*.produkt_id' => 'required|exists:produkty,id',
            'produkty.*.ilosc' => 'required|integer|min:1',
            'automat_id' => 'required|exists:automats,id', // poprawiona nazwa tabeli
        ]);

        $zamowienie = Zamowienie::create([
            'data_realizacji' => now()->addDay(),
            'automat_id' => $request->get('automat_id'), // poprawiony klucz
        ]);

        foreach ($request->produkty as $pozycja) {
            $zamowienie->produkty()->attach($pozycja['produkt_id'], ['ilosc' => $pozycja['ilosc']]);
        }

         return redirect()->route('zamowienia.index', [
        'automat_id' => $request->get('automat_id')
        ])->with('success', 'Zamówienie zostało zapisane.');
    }






    public function archiwum()
    {
        $zamowienia = Zamowienie::orderByDesc('data_zamowienia')->paginate(20);
        return view('zamowienia.archiwum', compact('zamowienia'));
    }


    /**
     * Display the specified resource.
     */
    public function show(Zamowienie $zamowienie)
    {
        // Załaduj relację produktów, aby uniknąć lazy loadingu w widoku
        $zamowienie->load('produkty');

        return view('zamowienia.show', compact('zamowienie'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zamowienie $zamowienie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zamowienie $zamowienie)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zamowienie $zamowienie)
    {
        //
    }


   public function podsumowanieDnia($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->whereDate('zamowienia.data_zamowienia', $date);

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id'));
        }

        $produkty = $query
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get();

        return view('zamowienia.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $date->format('Y-m-d'),
            'typ' => 'Dzień',
        ]);
    }

    // Podsumowanie tygodnia
   public function podsumowanieTygodnia($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $start = $date->copy()->startOfWeek();
        $end = $date->copy()->endOfWeek();

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]);

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id'));
        }

        $produkty = $query
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get();

        return view('zamowienia.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y-m-d') . ' do ' . $end->format('Y-m-d'),
            'typ' => 'Tydzień',
        ]);
    }

    // Podsumowanie miesiąca
    public function podsumowanieMiesiaca($month = null)
    {
        $date = $month ? Carbon::parse($month) : Carbon::today();
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]);

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id'));
        }

        $produkty = $query
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get();

        return view('zamowienia.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y-m'),
            'typ' => 'Miesiąc',
        ]);
    }

    // Podsumowanie roku
    public function podsumowanieRoku($year = null)
    {
        $date = $year ? Carbon::parse($year . '-01-01') : Carbon::today();
        $start = $date->copy()->startOfYear();
        $end = $date->copy()->endOfYear();

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]);

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id'));
        }

        $produkty = $query
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get();

        return view('zamowienia.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y'),
            'typ' => 'Rok',
        ]);
    }
}
