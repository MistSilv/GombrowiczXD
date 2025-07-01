<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduktWsad extends Model
{
    use HasFactory;

    protected $fillable = [
        'wsad_id',
        'produkt_id',
        'ilosc',
    ];

    public function wsad()
    {
        return $this->belongsTo(Wsad::class);
    }

    public function produkt()
    {
        return $this->belongsTo(Produkt::class);
    }

    protected $table = 'produkt_wsad';
}
