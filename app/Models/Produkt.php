<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produkt extends Model
{
    use HasFactory;
    
    protected $table = 'produkty';

    public $timestamps = false;

    protected $fillable = ['id','tw_nazwa', 'tw_idabaco', 'is_wlasny'];

    public function zamowienia()
    {
        return $this->belongsToMany(Zamowienie::class, 'produkt_zamowienie')
                    ->withPivot('ilosc');
    }

    public function eanCodes()
    {
        return $this->hasMany(EanCode::class, 'produkt_id');
    }

    public function wsady()
    {
        return $this->belongsToMany(Wsad::class, 'produkt_wsad')
                    ->withPivot('ilosc');
    }


    public function straty()
    {
        return $this->belongsToMany(Strata::class, 'produkt_strata')
                    ->withPivot('ilosc');
    }

    public function isWlasny(): bool
    {
        return (bool) $this->is_wlasny;
    }

    public function isObcy(): bool
    {
        return !$this->isWlasny();
    }

}