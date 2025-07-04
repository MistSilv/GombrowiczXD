<?php

namespace App\Http\Controllers;

use App\Models\Zamowienie;
use App\Models\Produkt;
use App\Models\Automat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ZamowienieMail;
use App\Exports\ZamowienieExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\PDF;



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
        }); // Filtruj zamówienia, które nie mają daty realizacji lub mają datę realizacji dzisiejszą lub późniejszą

        if ($request->has('automat_id')) {
            $query->where('automat_id', $request->automat_id);
        } // Sprawdź, czy automat_id jest w żądaniu i dodaj warunek

        $zamowienia = $query->orderByDesc('data_zamowienia')->get(); // Pobierz zamówienia z bazy danych, posortowane malejąco według daty zamówienia

        return view('zamowienia.index', compact('zamowienia'));
    }


    /**
     * Show the form for creating a new resource.
     */


   public function create(Request $request)
{
    // Pobierz tylko produkty własne dla standardowego zamówienia
    $produkty = Produkt::where('is_wlasny', true)->orderBy('tw_nazwa')->get();
    
    $automatId = $request->get('automat_id');
    $automat = $automatId ? Automat::findOrFail($automatId) : null; 

    return view('zamowienia.create', compact('produkty', 'automat'));
}

    public function createProdukcja(Request $request)
    {
        $produkty = Produkt::where('is_wlasny', true)->orderBy('tw_nazwa')->get();
        $automatId = $request->get('automat_id');
        $automat = $automatId ? Automat::findOrFail($automatId) : null; 

        return view('zamowienia.create', compact('produkty', 'automat'));
    }



    public function store(Request $request)
{
    $request->validate([
        'produkty' => 'required|array|min:1',
        'produkty.*.produkt_id' => 'required|exists:produkty,id',
        'produkty.*.ilosc' => 'required|integer|min:1|max:2147483647',
        'automat_id' => 'required|exists:automats,id',
    ]);

    $zamowienie = Zamowienie::create([
        'data_realizacji' => now()->addDay(),
        'automat_id' => $request->get('automat_id'),
    ]);

    foreach ($request->produkty as $pozycja) {
        $zamowienie->produkty()->attach($pozycja['produkt_id'], ['ilosc' => $pozycja['ilosc']]);
    }

    // Tworzenie pliku XLSX
    $xlsxContent = Excel::raw(new ZamowienieExport($zamowienie), \Maatwebsite\Excel\Excel::XLSX);

    // Wysyłka maila z załącznikiem XLSX
    Mail::to('domgggzzz@gmail.com')->send(new ZamowienieMail($xlsxContent, $zamowienie));

    return redirect()->route('zamowienia.index', ['automat_id' => $request->get('automat_id')])
        ->with('success', 'Zamówienie zostało zapisane i mail wysłany.');
}

    public function archiwum()
    {
        $zamowienia = Zamowienie::orderByDesc('data_zamowienia')->paginate(20); // Pobierz zamówienia z bazy danych, posortowane malejąco według daty zamówienia
        return view('zamowienia.archiwum', compact('zamowienia'));
    }


    /**
     * Display the specified resource.
     */
    public function show(Zamowienie $zamowienie)
    {
        $zamowienie->load(['produkty', 'automat']); 

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

    // Podsumowanie dnia
   public function podsumowanieDnia($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today(); // Użyj Carbon do parsowania daty lub ustaw dzisiejszą datę

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->whereDate('zamowienia.data_zamowienia', $date); // Filtruj po dacie zamówienia

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id')); // Sprawdź, czy automat_id jest w żądaniu i dodaj warunek
        }

        $produkty = $query
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get(); // Pobierz produkty z sumą ilości zamówień

        return view('zamowienia.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $date->format('Y-m-d'),
            'typ' => 'Dzień', // Typ podsumowania
        ]);
    }

    // Podsumowanie tygodnia
   public function podsumowanieTygodnia($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today(); // Użyj Carbon do parsowania daty lub ustaw dzisiejszą datę
        $start = $date->copy()->startOfWeek(); // Początek tygodnia
        $end = $date->copy()->endOfWeek(); // Koniec tygodnia

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]); // Filtruj po zakresie dat zamówienia

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id')); // Sprawdź, czy automat_id jest w żądaniu i dodaj warunek
        }

        $produkty = $query
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get(); // Pobierz produkty z sumą ilości zamówień

        return view('zamowienia.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y-m-d') . ' do ' . $end->format('Y-m-d'),
            'typ' => 'Tydzień', // Typ podsumowania
        ]);
    }

    // Podsumowanie miesiąca
    public function podsumowanieMiesiaca($month = null)
    {
        $date = $month ? Carbon::parse($month) : Carbon::today(); // Użyj Carbon do parsowania miesiąca lub ustaw dzisiejszą datę
        $start = $date->copy()->startOfMonth(); // Początek miesiąca
        $end = $date->copy()->endOfMonth(); // Koniec miesiąca

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]); // Filtruj po zakresie dat zamówienia

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id')); // Sprawdź, czy automat_id jest w żądaniu i dodaj warunek
        }

        $produkty = $query
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get(); // Pobierz produkty z sumą ilości zamówień

        return view('zamowienia.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y-m'),
            'typ' => 'Miesiąc', // Typ podsumowania
        ]);
    }

    // Podsumowanie roku
    public function podsumowanieRoku($year = null)
    {
        $date = $year ? Carbon::parse($year . '-01-01') : Carbon::today(); // Użyj Carbon do parsowania roku lub ustaw dzisiejszą datę
        $start = $date->copy()->startOfYear(); // Początek roku
        $end = $date->copy()->endOfYear(); // Koniec roku

        $query = DB::table('produkt_zamowienie')
            ->join('zamowienia', 'produkt_zamowienie.zamowienie_id', '=', 'zamowienia.id')
            ->join('produkty', 'produkt_zamowienie.produkt_id', '=', 'produkty.id')
            ->whereBetween('zamowienia.data_zamowienia', [$start, $end]); // Filtruj po zakresie dat zamówienia

        if (request()->filled('automat_id')) {
            $query->where('zamowienia.automat_id', request('automat_id')); // Sprawdź, czy automat_id jest w żądaniu i dodaj warunek
        }

        $produkty = $query
            ->select('produkty.tw_nazwa', DB::raw('SUM(produkt_zamowienie.ilosc) as suma'))
            ->groupBy('produkty.tw_nazwa')
            ->get(); // Pobierz produkty z sumą ilości zamówień

        return view('zamowienia.podsumowanie', [
            'produkty' => $produkty,
            'okres' => $start->format('Y'),
            'typ' => 'Rok', // Typ podsumowania
        ]);
    }



    //testowe rzeczy tutaj potem się zakomentuuje inacznej
    public function pobierzZamowienieXlsx($id)
{
    $zamowienie = Zamowienie::with('produkty')->findOrFail($id);

    return Excel::download(new ZamowienieExport($zamowienie), "zamowienie_{$id}.xlsx");
}

public function pobierzZamowienieCsv($id)
{
    $zamowienie = Zamowienie::with('produkty')->findOrFail($id);

    return Excel::download(new ZamowienieExport($zamowienie), "zamowienie_{$id}.csv", \Maatwebsite\Excel\Excel::CSV);
}
}
