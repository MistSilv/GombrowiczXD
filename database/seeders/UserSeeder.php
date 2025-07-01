<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('1234567890'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Admin2',
            'email' => 'domgggzzz@gmail.com',
            'password' => Hash::make('1234567890'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Serwis',
            'email' => 'serwis@test.com',
            'password' => Hash::make('1234567890'),
            'role' => 'serwis',
        ]);

        User::create([
            'name' => 'Produkcja',
            'email' => 'produkcja@test.com',
            'password' => Hash::make('1234567890'),
            'role' => 'produkcja',
        ]);
    }
}