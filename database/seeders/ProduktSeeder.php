<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produkt;

class ProduktSeeder extends Seeder
{
    public function run(): void
    {
        // Dodaj produkty własne (50 szt.)
        Produkt::factory()->count(50)->create([
            'is_wlasny' => true,
        ]);
    }
}
