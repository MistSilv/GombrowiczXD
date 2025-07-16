<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wsad;
use App\Models\Produkt;
use App\Models\ProduktWsad;
use Illuminate\Support\Arr;

class WsadSeeder extends Seeder
{
    public function run(): void
    {
        $produkty = \App\Models\Produkt::all();

        \App\Models\Wsad::factory(300)->create()->each(function ($wsad) use ($produkty) {
            $maxProduktow = min(30, $produkty->count());
            $minProduktow = min(10, $maxProduktow); // np. jak masz tylko 5 produktów
            $ileProduktow = rand($minProduktow, $maxProduktow);

            $produktyDoWsadu = $produkty->random($ileProduktow);

            foreach ($produktyDoWsadu as $produkt) {
                if (rand(1, 100) <= 70) {
                    \App\Models\ProduktWsad::create([
                        'wsad_id' => $wsad->id,
                        'produkt_id' => $produkt->id,
                        'ilosc' => rand(1, 20),
                    ]);
                }
            }
        });
    }

}
