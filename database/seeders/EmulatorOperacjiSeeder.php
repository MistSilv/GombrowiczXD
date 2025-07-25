<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produkt;
use App\Models\Zamowienie;
use App\Models\Wsad;
use App\Models\ProduktWsad;
use App\Models\Automat;
use Illuminate\Support\Carbon;

class EmulatorOperacjiSeeder extends Seeder
{
    public function run(): void
    {
        $maxStanMagazynowy = 4500; // max stan magazynowy
        $maxZamowienie = 3000;     // max zamówienie (ilość sztuk pojedynczego zamówienia)

        $produkty = Produkt::all();
        $count = max(1, (int)($produkty->count() * 0.1));
        $produktyWybrane = $produkty->random($count);

        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2025, 6, 30);

        $sumaZamowien = [];
        $sumaWsadow = [];

        foreach ($produktyWybrane as $produkt) {
            $sumaZamowien[$produkt->id] = 0;
            $sumaWsadow[$produkt->id] = 0;
        }

        $automaty = Automat::all();

        // Batch daty: 14-16 oraz ostatni dzień miesiąca
        function getBatchDates(Carbon $start, Carbon $end): array {
            $dates = [];
            $current = $start->copy()->startOfMonth();

            while ($current <= $end) {
                $midDay = rand(14, 16);
                $dates[] = $current->copy()->day($midDay);            // środek miesiąca
                $dates[] = $current->copy()->endOfMonth();            // koniec miesiąca
                $current->addMonth();
            }

            return collect($dates)
                ->unique(fn($date) => $date->toDateString())
                ->filter(fn($date) => $date >= $start && $date <= $end)
                ->sort()
                ->values()
                ->all();
        }

        $batchDates = getBatchDates($startDate, $endDate);

        foreach ($batchDates as $dataBatch) {
            // Zamówienia
            foreach ($produktyWybrane as $produkt) {
                $produktId = $produkt->id;
                $stan = $sumaZamowien[$produktId] - $sumaWsadow[$produktId];

                if ($stan >= $maxStanMagazynowy) continue;

                $maxDoZamowienia = $maxStanMagazynowy - $stan;
                $ilosc = rand(1, min($maxZamowienie, $maxDoZamowienia));

                $zamowienie = Zamowienie::factory()->create([
                    'data_zamowienia' => $dataBatch,
                    'data_realizacji' => (clone $dataBatch)->addDays(rand(1, 14)),
                    'automat_id' => $automaty->random()->id,
                ]);

                $zamowienie->produkty()->attach($produktId, ['ilosc' => $ilosc]);
                $sumaZamowien[$produktId] += $ilosc;
            }

            // Wsady
            foreach ($produktyWybrane as $produkt) {
                $produktId = $produkt->id;
                $stan = $sumaZamowien[$produktId] - $sumaWsadow[$produktId];
                if ($stan <= 0) continue;

                $doWsadu = (int) round($stan * rand(10, 50) / 100);
                if ($doWsadu <= 0) continue;

                while ($doWsadu > 0) {
                    $batch = min($doWsadu, rand(200, 1000));

                    $wsad = Wsad::factory()->create([
                        'data_wsadu' => $dataBatch->copy()->setTime(rand(6, 20), rand(0, 59), rand(0, 59)),
                        'automat_id' => $automaty->random()->id,
                    ]);

                    ProduktWsad::create([
                        'wsad_id' => $wsad->id,
                        'produkt_id' => $produktId,
                        'ilosc' => $batch,
                    ]);

                    $sumaWsadow[$produktId] += $batch;
                    $doWsadu -= $batch;
                }
            }
        }
    }
}
