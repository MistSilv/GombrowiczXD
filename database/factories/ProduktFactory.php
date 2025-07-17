<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProduktFactory extends Factory
{
    protected static array $usedNames = [];

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

        // Spróbuj wziąć unikalną nazwę z listy
        $availableNames = array_diff($nazwy, self::$usedNames);

        if (!empty($availableNames)) {
            $nazwa = $this->faker->randomElement($availableNames);
            self::$usedNames[] = $nazwa;
        } else {
            // Fallback na losowe miasto (nie unikalne)
            $nazwa = $this->faker->city();
        }

        return [
            'tw_nazwa' => $nazwa,
            'tw_idabaco' => null,
            'is_wlasny' => true,
        ];
    }
}
