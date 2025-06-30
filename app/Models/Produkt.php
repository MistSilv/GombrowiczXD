<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produkt extends Model
{
    protected $table = 'produkty';

    public $timestamps = false;

    protected $fillable = ['tw_nazwa', 'tw_idabaco']; // pola, które można masowo przypisywać

    public function zamowienia()
    {
        return $this->belongsToMany(Zamowienie::class, 'produkt_zamowienie')
                    ->withPivot('ilosc'); // relacja z zamówieniami przez tabelę przestawną
    }

    public function eanCodes()
    {
        return $this->hasMany(EanCode::class); // relacja z kodami EAN
    }

}