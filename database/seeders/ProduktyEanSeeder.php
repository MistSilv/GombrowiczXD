<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Produkt;
use App\Models\EanCode;

class ProduktyEanSeeder extends Seeder
{
    // Lista kodów EAN do pobrania
    protected $eanCodes = [
        "5901784166430",
        "5904441234237",
        "5901784166898",
        "5901784165839",
        "5901784167345",
        // Dodaj kolejne kody EAN tutaj
    ];

    public function run()
    {
        foreach ($this->eanCodes as $ean) {
            // Pobierz dane produktu z endpointa
            $response = Http::timeout(5)->get("http://192.168.210.219/automaty/wyszukaj_towar.php?search={$ean}");

            if ($response->successful()) {
                $data = $response->json();

                // Jeśli odpowiedź jest tablicą z jednym produktem
                if (is_array($data) && isset($data[0])) {
                    $item = $data[0];

                    if (
                        isset($item['idabaco']) &&
                        isset($item['nazwa_towaru']) &&
                        isset($item['kody_plu']) &&
                        is_array($item['kody_plu'])
                    ) {
                        $produkt = Produkt::updateOrCreate(
                            [
                                'tw_idabaco' => $item['idabaco'],
                            ],
                            [
                                'tw_nazwa' => $item['nazwa_towaru'],
                                'is_wlasny' => false,
                            ]
                        );

                        foreach ($item['kody_plu'] as $kodEan) {
                            EanCode::updateOrCreate(
                                [
                                    'kod_ean' => $kodEan,
                                    'produkt_id' => $produkt->id,
                                ]
                            );
                        }
                    } else {
                        dump("Brak wymaganych danych dla EAN: {$ean}", $item);
                    }
                } else {
                    dump("Brak wymaganych danych dla EAN: {$ean}", $data);
                }
            }
        }
    }
}