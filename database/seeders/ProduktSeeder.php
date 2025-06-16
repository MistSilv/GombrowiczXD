<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Produkt;
use App\Models\EanKod;


class ProduktSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produkty = [
            [
                'tw_nazwa' => 'Coca‑Cola 330 ml', 
                'tw_idabaco' => null, 
                'ean_kody' => ['5449000000996', '4008400402222'] // przykładowe różne kody
            ],
            [
                'tw_nazwa' => 'Pepsi 500 ml PET', 
                'tw_idabaco' => null, 
                'ean_kody' => ['3800747001134']
            ],
            [
                'tw_nazwa' => 'Pepsi 850 ml PET', 
                'tw_idabaco' => null, 
                'ean_kody' => ['5900497311502']
            ],
            [
                'tw_nazwa' => 'Tarczyński Kabanos Exclusive Chilli', 
                'tw_idabaco' => null, 
                'ean_kody' => ['5908230521522']
            ],
            [
                'tw_nazwa' => '7Days Croissant Super Max Vanilla', 
                'tw_idabaco' => null, 
                'ean_kody' => ['5201360535705']
            ],
            [
                'tw_nazwa' => '7Days Croissant Truskawka', 
                'tw_idabaco' => null, 
                'ean_kody' => ['5201360535706']
            ],
            [
                'tw_nazwa' => 'Bułka z szynką', 
                'tw_idabaco' => null, 
                'ean_kody' => []
            ],
            [
                'tw_nazwa' => 'Bułka z tuńczykiem', 
                'tw_idabaco' => null, 
                'ean_kody' => []
            ],
            [
                'tw_nazwa' => 'Bułka z serem', 
                'tw_idabaco' => null, 
                'ean_kody' => []
            ],
        ];

        foreach ($produkty as $produktData) {
            $eanKody = $produktData['ean_kody'];
            unset($produktData['ean_kody']);

            $produkt = Produkt::create($produktData);

            foreach ($eanKody as $kod) {
                $produkt->eanKody()->create(['kod_ean' => $kod]);
            }
        }
    }
}