<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EanCode extends Model
{
    protected $table = 'ean_codes'; 

    protected $fillable = ['produkt_id', 'kod_ean']; 

    public $timestamps = false;
    
    public function produkt()
    {
        return $this->belongsTo(Produkt::class, 'produkt_id'); 
    }
}
