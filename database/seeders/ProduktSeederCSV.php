<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produkt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProduktSeederCSV extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/produkty.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("Plik CSV nie został znaleziony: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle, 1000, ';');

        // Usuń BOM jeśli występuje
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        }

        $groupedProdukty = [];

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $data = array_combine($header, $row);
            $id = $data['id'] ?? null;

            if (!isset($groupedProdukty[$id])) {
                $groupedProdukty[$id] = [
                    'tw_nazwa' => $data['tw_nazwa'],
                    'tw_idabaco' => $data['tw_idabaco'] ?: null,
                    'is_wlasny' => false,
                    'ean_kody' => [],
                ];
            }

            if (!empty($data['ean_kody']) && !in_array($data['ean_kody'], $groupedProdukty[$id]['ean_kody'])) {
                $groupedProdukty[$id]['ean_kody'][] = $data['ean_kody'];
            }
        }

        fclose($handle);

        DB::transaction(function () use ($groupedProdukty) {
            foreach ($groupedProdukty as $produktData) {
                $eanKody = $produktData['ean_kody'];
                unset($produktData['ean_kody']);

                $produkt = \App\Models\Produkt::create($produktData);

                $now = now();
                $eanInsert = [];

                foreach ($eanKody as $ean) {
                    $eanInsert[] = [
                        'produkt_id' => $produkt->id,
                        'kod_ean' => (string) $ean
                    ];
                }

                if (!empty($eanInsert)) {
                    DB::table('ean_codes')->insert($eanInsert);
                }
            }
        });

        $this->command->info("Zaimportowano " . count($groupedProdukty) . " produktów z CSV.");
    }
}
