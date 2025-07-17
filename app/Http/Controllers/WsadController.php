<?php

namespace App\Http\Controllers;

use App\Models\Wsad;
use App\Models\Produkt;
use App\Models\ProduktWsad;
use App\Models\Automat;
use Illuminate\Http\Request;

class WsadController extends Controller
{
   public function index(Request $request)
    {
        $automatId = $request->get('automat_id');
        $automat = $automatId ? Automat::findOrFail($automatId) : null;

        $query = Wsad::with(['automat', 'produkty'])
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month);

        if ($automat) {
            $query->where('automat_id', $automat->id);
        }

        $query->getQuery()->orders = null;

        $query = $query->orderBy('created_at', 'desc');

        $wsady = $query->paginate(20);

        $produkty = Produkt::orderBy('tw_nazwa')->get();

        $wsadProdukty = collect();
        if ($automat) {
            $ostatniWsad = Wsad::where('automat_id', $automat->id)->latest()->first();
            if ($ostatniWsad) {
                $wsadProdukty = $ostatniWsad->produkty()->withPivot('ilosc')->get();
            }
        }

        return view('wsady.index', compact('wsady', 'automat', 'produkty', 'wsadProdukty'));
    }


    public function create(Request $request)
    {
        $automat = null;
        if ($request->has('automat_id')) {
            $automat = Automat::find($request->get('automat_id'));
        }

        $produkty = Produkt::orderBy('tw_nazwa')->get();

        // Zawsze ustawiamy kolekcję, nawet pustą
        $wsadProdukty = collect();
        if ($automat) {
            $ostatniWsad = Wsad::where('automat_id', $automat->id)->latest()->first();
            if ($ostatniWsad) {
                $wsadProdukty = $ostatniWsad->produkty()->withPivot('ilosc')->get();
            }
        }

        return view('wsady.create', compact('automat', 'produkty', 'wsadProdukty'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'automat_id' => 'required|exists:automats,id',
            'produkty' => 'required|array',
            'produkty.*.produkt_id' => 'required|exists:produkty,id',
            'produkty.*.ilosc' => 'required|integer|min:1 | max:3000',
        ]);

        $wsad = Wsad::create([
            'automat_id' => $request->automat_id,
            'data_wsadu' => now(),
        ]);

        foreach ($request->produkty as $produkt) {
            ProduktWsad::create([
                'wsad_id' => $wsad->id,
                'produkt_id' => $produkt['produkt_id'],
                'ilosc' => $produkt['ilosc'],
            ]);
        }

        return redirect()->route('wsady.create', ['automat_id' => $request->automat_id])
                         ->with('success', 'Wsad został dodany.');
    }

    public function show($id)
    {
        $wsad = Wsad::with(['automat', 'produkty'])->findOrFail($id);

        return view('wsady.show', compact('wsad'));
    }


    // Usuń produkt z ostatniego wsadu automatu
    public function delete(Request $request)
    {
        $produktId = $request->route('produkt_id');
        $automatId = $request->route('automat_id');

        $ostatniWsad = Wsad::where('automat_id', $automatId)->latest()->first();

        if (!$ostatniWsad) {
            return redirect()->back()->withErrors('Nie znaleziono wsadu dla tego automatu.');
        }

        $produktWsad = ProduktWsad::where('wsad_id', $ostatniWsad->id)
            ->where('produkt_id', $produktId)
            ->first();

        if (!$produktWsad) {
            return redirect()->back()->withErrors('Produkt nie istnieje w tym wsadzie.');
        }

        $produktWsad->delete();

        return redirect()->back()->with('success', 'Produkt został usunięty z wsadu.');
    }

    public function archiwum(Request $request)
    {
        $wsady = Wsad::with(['automat', 'produkty'])->orderBy('created_at', 'desc')->paginate(30);

        return view('wsady.archiwum', compact('wsady'));
    }

}
