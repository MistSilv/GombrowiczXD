<?php

namespace App\Http\Controllers;

use App\Models\Strata;
use App\Models\Produkt;
use App\Models\Automat;
use Illuminate\Http\Request;

class StrataController extends Controller
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
}
