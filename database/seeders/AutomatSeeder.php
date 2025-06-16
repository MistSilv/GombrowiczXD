<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Automat;

class AutomatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $automaty = [
            [
                'nazwa' => 'Mendelez Jarosław - Automat nr 1',
                'lokalizacja' => 'Piekarska 4, 37-500 Jarosław',
            ],
            [
                'nazwa' => 'Kopernik - Liceum Ogólnokształcące nr 1 - Automat nr 1',
                'lokalizacja' => '3go maja 4, 37-500 Jarosław',
            ],
            [
                'nazwa' => 'Lclerc Jarosław - Automat nr 1',
                'lokalizacja' => 'gen. Władysława Eugeniusza Sikorskiego 2a, 37-500 Jarosław',
            ],
        ];

        foreach ($automaty as $automatData) {
            Automat::create($automatData);
        }


    }
}
