<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strata extends Model
{
    protected $table = 'straty';

    protected $fillable = [
        'automat_id',
        'data_straty',
        'opis',
    ];

    public function automat()
    {
        return $this->belongsTo(Automat::class);
    }

    public function produkty()
    {
        return $this->belongsToMany(Produkt::class, 'produkt_strata')
                    ->withPivot('ilosc')
                    ->withTimestamps();
    }
}