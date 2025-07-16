<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        
        $this->call(ProduktSeeder::class);
        $this->call(AutomatSeeder::class);
        $this->call(UserSeeder::class);
       //$this->call(WsadSeeder::class);// zakomentować aby nie tworzyć wsadów

    }
}
