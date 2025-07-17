<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produkt>
 */
class ProduktFactory extends Factory
{
    public function definition(): array
    {
        $nazwy = [
            'Bułka z szynką', 'Bułka z tuńczykiem', 'Bułka z serem', 'Bułka z jajkiem',
            'Kanapka z kurczakiem', 'Kanapka z wołowiną', 'Kanapka z łososiem',
            'Bagietka francuska', 'Wrap z kurczakiem', 'Wrap vege', 'Sałatka grecka',
            'Sałatka cezar', 'Bułka fitness', 'Bułka pełnoziarnista z serem',
            'Tost z szynką i serem', 'Tost z mozzarellą', 'Panini z kurczakiem',
            'Panini vege', 'Mini pizza', 'Zapiekanka z pieczarkami'
        ];

        return [
            'tw_nazwa' => $this->faker->unique()->randomElement($nazwy),
            'tw_idabaco' => null,
            'is_wlasny' => true,
        ];
    }
}
