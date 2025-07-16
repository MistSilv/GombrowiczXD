<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Automat;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wsad>
 */
class WsadFactory extends Factory
{
    public function definition(): array
    {
        $data = $this->faker->dateTimeBetween('2025-01-01', '2025-06-30');

        return [
            'data_wsadu' => $data,
            'created_at' => $data,
            'updated_at' => $data,
            'automat_id' => Automat::inRandomOrder()->first()?->id, // zapobiega błędowi przy pustej bazie
        ];
    }
}
