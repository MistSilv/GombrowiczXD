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
     * Lista strat (np. aktualny miesiąc)
     */
    public function index()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $straty = Strata::with('automat')
            ->whereBetween('data_straty', [$start, $end])
            ->orderByDesc('data_straty')
            ->paginate(20);

        return view('straty.index', compact('straty'));
    }

    /**
     * Archiwum strat (wszystkie)
     */
    public function archiwum(Request $request)
    {
        $query = Strata::with('automat');

        $minRaw = $request->min_date;
        $maxRaw = $request->max_date;

        if ($request->filled('min_date') && $request->filled('max_date')) {
            $minDate = $minRaw . ' 00:00:00';
            $maxDate = $maxRaw . ' 23:59:59';
            $query->whereBetween('data_straty', [$minDate, $maxDate]);
        } else {
            if ($request->filled('min_date')) {
                $query->where('data_straty', '>=', $minRaw . ' 00:00:00');
            }
            if ($request->filled('max_date')) {
                $query->where('data_straty', '<=', $maxRaw . ' 23:59:59');
            }
        }

        $straty = $query->orderBy('data_straty', 'desc')->paginate(20)->withQueryString();

        return view('straty.archiwum', compact('straty'));
    }


    /**
     * Formularz tworzenia nowej straty
     * Przekazuje listę produktów (do JS) i opcjonalnie automat
     */
    public function create(Request $request)
    {
        $produkty = Produkt::orderBy('tw_nazwa')->get();
        $automatId = $request->get('automat_id');
        $automat = $automatId ? Automat::find($automatId) : null;

        return view('straty.create', compact('produkty', 'automat'));
    }

    /**
     * Zapis nowej straty z powiązanymi produktami i ilościami
     */
    public function store(Request $request)
    {
        $request->validate([
            'automat_id' => 'required|exists:automats,id',
            'data_straty' => 'required|date',
            'opis' => 'nullable|string',
            'produkty' => 'required|array|min:1',
            'produkty.*.produkt_id' => 'required|exists:produkty,id',
            'produkty.*.ilosc' => 'required|integer|min:1|max:3000',
        ]);

        // Tworzymy rekord straty
        $strata = Strata::create([
            'automat_id' => $request->input('automat_id'),
            'data_straty' => $request->input('data_straty'),
            'opis' => $request->input('opis'),
        ]);

        // Dodajemy powiązania produktów do straty
        foreach ($request->produkty as $pozycja) {
            $strata->produkty()->attach($pozycja['produkt_id'], ['ilosc' => $pozycja['ilosc']]);
        }

        return redirect()->route('zamowienia.create', ['automat_id' => $request->automat_id])
            ->with('success', 'Straty zostały zapisane.');
    }

    /**
     * Pojedyncza strata - widok szczegółowy
     */
    public function show(Strata $strata)
    {
        $strata->load('automat');

        $produkty = $strata->produkty()->paginate(10)->withQueryString();

        return view('straty.show', compact('strata', 'produkty'));
    }


    /**
     * Endpoint API do wyszukiwania produktów po nazwie (dla autocomplete)
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Produkt::select('id', 'tw_nazwa')
            ->where('tw_nazwa', 'like', "%{$q}%")
            ->orderBy('tw_nazwa')
            ->limit(20)
            ->get();

        return response()->json($results);
    }
}
