<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produkt;
use App\Models\EanCode;
use Illuminate\Support\Facades\DB;

class ProduktSeederCSV extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ścieżka względna do pliku CSV w katalogu database/data
        $csvPath = database_path('data/produkty.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("Plik CSV nie został znaleziony: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle, 1000, ';');

        if (isset($header[0])) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        }

        $groupedProdukty = [];

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $data = array_combine($header, $row);

            $id = $data['id'] ?? null;  // Zostawiamy, ale nie używamy

            if (!isset($groupedProdukty[$id])) {
                $groupedProdukty[$id] = [
                    'tw_nazwa' => $data['tw_nazwa'],
                    'tw_idabaco' => $data['tw_idabaco'],
                    'is_wlasny' => false,
                    'ean_kody' => [],
                ];
            }

            if (!in_array($data['ean_kody'], $groupedProdukty[$id]['ean_kody'])) {
                $groupedProdukty[$id]['ean_kody'][] = $data['ean_kody'];
            }
        }

        fclose($handle);

        foreach ($groupedProdukty as $id => $produktData) {
            $eanKody = $produktData['ean_kody'];
            unset($produktData['ean_kody']);

            // Tworzymy nowy produkt bez ręcznego id - Laravel wygeneruje ID automatycznie
            $produkt = Produkt::create($produktData);

            // Dodajemy kody EAN
            foreach ($eanKody as $kod) {
                $produkt->eanCodes()->firstOrCreate(['kod_ean' => $kod]);
            }
        }

        $this->command->info("Zaimportowano " . count($groupedProdukty) . " produktów z CSV.");
    }
}
