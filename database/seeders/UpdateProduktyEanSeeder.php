<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produkt;
use Illuminate\Support\Facades\DB;

class UpdateProduktyEanSeeder extends Seeder
{
    public function run()
    {
        $csvPath = database_path('data/produkty.csv');
        $rows = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Pomijamy nagłówek
        array_shift($rows);

        $eanMap = [];
        foreach ($rows as $row) {
            $fields = explode(';', $row);
            if (count($fields) < 4) continue;
            $idabaco = trim($fields[3]);
            $ean = trim($fields[1]);
            if (!isset($eanMap[$idabaco])) {
                $eanMap[$idabaco] = [];
            }
            $eanMap[$idabaco][] = $ean;
        }

        $produkty = Produkt::all();

        foreach ($produkty as $produkt) {
            $idabaco = $produkt->tw_idabaco; // popraw na tw_idabaco!
            if (isset($eanMap[$idabaco])) {
                foreach ($eanMap[$idabaco] as $kod_ean) {
                    // Sprawdź czy już istnieje taki kod dla produktu
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