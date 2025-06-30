<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zamowienie extends Model
{
    protected $table = 'zamowienia';

    protected $fillable = ['data_realizacji', 'automat_id']; // pola, które można masowo przypisywać

    public $timestamps = false;

    public function produkty()
    {
        return $this->belongsToMany(Produkt::class, 'produkt_zamowienie')
                    ->withPivot('ilosc'); // relacja z produktami przez tabelę przestawną
    }

    public function automat()
    {
        return $this->belongsTo(Automat::class, 'automat_id'); // relacja z modelem Automat
    }
}
