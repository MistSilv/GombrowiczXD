<?php

namespace App\Http\Controllers;

use App\Models\Wsad;
use App\Models\Produkt;
use App\Models\ProduktWsad;
use App\Models\Automat;
use Illuminate\Http\Request;

class WsadController extends Controller
{
    public function index()
    {
        $wsady = Wsad::with('automat', 'produkty.produkt')->latest()->get();
        return view('wsady.index', compact('wsady'));
    }

    public function create()
    {
        $automaty = Automat::all();
        $produkty = Produkt::all();
        return view('wsady.create', compact('automaty', 'produkty'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'automat_id' => 'required|exists:automats,id',
            'produkty' => 'required|array',
            'produkty.*.produkt_id' => 'required|exists:produkty,id',
            'produkty.*.ilosc' => 'required|integer|min:1',
        ]);

        $wsad = Wsad::create([
            'automat_id' => $request->automat_id,
            'data_wsad-u' => now(), // lub z requestu jeśli masz
        ]);

        foreach ($request->produkty as $produkt) {
            ProduktWsad::create([
                'wsad_id' => $wsad->id,
                'produkt_id' => $produkt['produkt_id'],
                'ilosc' => $produkt['ilosc'],
            ]);
        }

        return redirect()->route('wsady.index')->with('success', 'Wsad został dodany.');
    }

    public function show(Wsad $wsad)
    {
        $wsad->load('automat', 'produkty.produkt');
        return view('wsady.show', compact('wsad'));
    }
}
