<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EanCode;

class EanController extends Controller
{
    public function checkEan(Request $request)
    {
        $request->validate([
            'kod_ean' => 'required|string'
        ]);

        $ean = EanCode::with('produkt')->where('kod_ean', $request->kod_ean)->first();

        if (!$ean) {
            return response()->json(['success' => false, 'message' => 'Kod EAN nie znaleziony'], 404);
        }

        return response()->json([
            'success' => true,
            'produkt' => [
                'id' => $ean->produkt->id,
                'tw_nazwa' => $ean->produkt->tw_nazwa,
                'tw_idabaco' => $ean->produkt->tw_idabaco,
                'kod_ean' => $ean->kod_ean
            ]
        ]);
    }
}

