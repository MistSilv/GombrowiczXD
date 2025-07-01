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

        $query = Wsad::with('automat', 'produkty.produkt')->latest();

        if ($automat) {
            $query->where('automat_id', $automat->id);
        }

        $wsady = $query->get();

        $produkty = Produkt::orderBy('tw_nazwa')->get();

        return view('wsady.index', compact('wsady', 'automat', 'produkty'));
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
        'data_wsadu' => now(), 
        ]);


        foreach ($request->produkty as $produkt) {
            ProduktWsad::create([
                'wsad_id' => $wsad->id,
                'produkt_id' => $produkt['produkt_id'],
                'ilosc' => $produkt['ilosc'],
            ]);
        }

        return redirect()->route('wsady.index', ['automat_id' => $request->automat_id])
                     ->with('success', 'Wsad został dodany.');
    }

    public function show(Wsad $wsad)
    {
        $wsad->load('automat', 'produkty.produkt');
        return view('wsady.show', compact('wsad'));
    }
}
