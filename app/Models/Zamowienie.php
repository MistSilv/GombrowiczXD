<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zamowienie extends Model
{
    protected $table = 'zamowienia';

    protected $fillable = ['data_realizacji', 'automat_id'];

    public $timestamps = false;

    public function produkty()
    {
        return $this->belongsToMany(Produkt::class, 'produkt_zamowienie')
                    ->withPivot('ilosc');
    }

    public function automat()
    {
        return $this->belongsTo(Automat::class, 'automat_id');
    }
}
