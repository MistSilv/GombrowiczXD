<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Automat extends Model
{
    protected $table = 'automats';

    protected $fillable = ['nazwa', 'lokalizacja']; // pola, które można masowo przypisywać

    public $timestamps = false;

    public function zamowienia()
    {
        return $this->hasMany(Zamowienie::class,'vending_machine_id'); // relacja z zamówieniami
    }
}

