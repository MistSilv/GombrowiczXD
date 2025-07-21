<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduktZamowienie extends Model
{
    protected $table = 'produkt_zamowienie'; // tu nazwa tabeli z bazy danych

    public $timestamps = false; // jeśli tabela nie ma timestampów
}
