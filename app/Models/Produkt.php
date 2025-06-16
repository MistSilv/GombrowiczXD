<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produkt extends Model
{
    protected $table = 'produkty';

    public $timestamps = false;

    protected $fillable = ['tw_nazwa', 'tw_idabaco'];

    public function zamowienia()
    {
        return $this->belongsToMany(Zamowienie::class, 'produkt_zamowienie')
                    ->withPivot('ilosc');
    }

    public function eanKody()
    {
        return $this->hasMany(EanCode::class);
    }

}