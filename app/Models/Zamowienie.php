<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zamowienie extends Model
{
    use HasFactory;
    
    protected $table = 'zamowienia';

    protected $fillable = ['data_realizacji', 'automat_id']; 

    public $timestamps = false;

    protected $casts = [
        'data_realizacji' => 'string',
    ];

    public function produkty()
    {
        return $this->belongsToMany(Produkt::class, 'produkt_zamowienie')
                    ->withPivot('ilosc'); 
    }

    public function automat()
    {
        return $this->belongsTo(Automat::class, 'automat_id'); 
    }

    public function getDataRealizacjiFormattedAttribute()
    {
        if ($this->data_realizacji === 'dzisiaj') {
            return now()->format('d-m-Y');
        } elseif ($this->data_realizacji === 'jutro') {
            return now()->addDay()->format('d-m-Y');
        } elseif ($this->data_realizacji) {
            return \Carbon\Carbon::parse($this->data_realizacji)->format('d-m-Y');
        }

        return null;
    }

}
