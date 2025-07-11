<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EanCode extends Model
{
    protected $table = 'ean_codes'; // nie jest konieczne, ale dobrze mieć jawnie

    protected $fillable = ['produkt_id', 'kod_ean']; // pola, które można masowo przypisywać

    public function produkt()
    {
        return $this->belongsTo(Produkt::class, 'produkt_id'); // relacja z modelem Produkt
    }
}
