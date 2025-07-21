<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Automat;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zamowienie>
 */
class ZamowienieFactory extends Factory
{
    public function definition(): array
    {
        $dataZamowienia = $this->faker->dateTimeBetween('2025-01-01', '2025-06-30');
        $dataRealizacji = (clone $dataZamowienia)->modify('+'.rand(1,14).' days');

        return [
            'data_zamowienia' => $dataZamowienia,
            'data_realizacji' => $dataRealizacji,
            'automat_id' => Automat::inRandomOrder()->first()?->id,
            'created_at' => $dataZamowienia,
            'updated_at' => $dataZamowienia,
        ];
    }
}
