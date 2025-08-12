<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produkt;
use Illuminate\Support\Facades\DB;

class UpdateProduktyEanByNameSeeder extends Seeder
{
    public function run()
    {
        // Mapa: nazwa produktu => kody EAN (jako tablica)
        $eanByName = [
            'VITAMIN BOOST GRAPEFRUIT 750ml' => ['5904988310623'],
            'LIPTON NAPÓJ 500ML GREEN ZERO' => ['5900497041249'],
            'MARET CHRUP CHLEB 70G PIZZA' => ['3800205871255'],
            'MARET CHRUP CHLEB 70G CEB+ŚMIE' => ['3800205877325'],
            'MARET CHRUP CHLEB 70G POM+OLIWK' => ['3800205875109'],
            'MONTE BATON MLECZNY 29G' => ['4014500506999', '4014500515083'],
            'KUPIEC WAFLE RYŻ 30G CZEK MLECZ' => ['5906747172459'],
            'KUPIEC CIAST 50G ZBOŻ MALI+ŻURA' => ['5906747176884'],
            'PROST PALUSZKI 70G GRAHAM CEBUL' => ['8586011381694'],
            'PROST PALUSZKI 70G GRAHAM SEZAM' => ['8586011381687'],
            'COCA COLA NAPÓJ GAZ 330ML ZERO' => ['5449000131805'],
            'PEPSI 330ML COLA ZERO' => ['5900497300339'],
            'TYMBARK SOK 300ML 100% MULT' => ['5900334001818'],
            'COCA COLA NAPÓJ GAZ 500ML ZERO' => ['5449000131836'],
            'PEPSI 500ML MAX' => ['5900497300506'],
            'MLE WYPAS MLEKO 200ML UHT WANIL' => ['5900512300535', '5900512320663'],
            'MLE WYPAS MLEKO 200ML UHT CZEKO' => ['5900512300542'],
        ];

        foreach ($eanByName as $nazwa => $kodyEan) {
            $produkty = Produkt::where('tw_nazwa', $nazwa)->get();
            foreach ($produkty as $produkt) {
                foreach ($kodyEan as $kod_ean) {
                    $exists = DB::table('ean_codes')
                        ->where('produkt_id', $produkt->id)
                        ->where('kod_ean', $kod_ean)
                        ->exists();
                    if (!$exists) {
                        DB::table('ean_codes')->insert([
                            'produkt_id' => $produkt->id,
                            'kod_ean' => $kod_ean,
                        ]);
                    }
                }
            }
        }
    }
}