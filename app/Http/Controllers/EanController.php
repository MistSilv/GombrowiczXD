<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EanCode;
use Illuminate\Support\Facades\Http;

class EanController extends Controller
{
    public function checkEan(Request $request)
    {
        
        $request->validate([
            'kod_ean' => 'required|string'
        ]);

        $ean = EanCode::with('produkt')
            ->where('kod_ean', $request->kod_ean)
            ->first();

        if (!$ean) {
            return response()->json([
                'success' => false,
                'message' => 'Kod EAN nie znaleziony w lokalnej bazie'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'produkt' => [
                'id' => $ean->produkt->id,
                'tw_nazwa' => $ean->produkt->tw_nazwa,
                'tw_idabaco' => $ean->produkt->tw_idabaco,
                'kod_ean' => $ean->kod_ean,
                'ean_codes' => [$ean->kod_ean], // Zwracamy jako array dla spójności
                'istnieje_w_bazie' => true
            ]
        ]);
        
    }
    public function checkEan1(Request $request)
    {
        $request->validate([
            'kod_ean' => 'required|string'
        ]);

        $ean = $request->kod_ean;
        $url = "http://192.168.210.219/automaty/wyszukaj_towar.php?search=" . urlencode($ean);

        try {
            $response = Http::timeout(5)->get($url);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Błąd połączenia z systemem sklepowym'
                ], 500);
            }

            $data = $response->json();

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nie znaleziono produktu dla podanego kodu: ' . $ean
                ], 404);
            }

            $produktSklep = $data[0];

            // Sprawdź czy produkt już istnieje w lokalnej bazie
            $produktLokalny = EanCode::with('produkt')
                ->whereIn('kod_ean', array_merge([$ean], $produktSklep['kody_plu'] ?? []))
                ->first();

            return response()->json([
                'success' => true,
                'produkt' => [
                    'id' => $produktLokalny ? $produktLokalny->produkt->id : null,
                    'tw_nazwa' => $produktSklep['nazwa_towaru'],
                    'tw_idabaco' => $produktSklep['idabaco'],
                    'kod_ean' => $ean,
                    'ean_codes' => $produktSklep['kody_plu'] ?? [], // Zmiana nazwy z kody_plu na ean_codes
                    'istnieje_w_bazie' => $produktLokalny !== null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Błąd systemowy: ' . $e->getMessage()
            ], 500);
        }
    }

}
