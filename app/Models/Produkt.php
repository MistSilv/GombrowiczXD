<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produkt extends Model
{

    use HasFactory;
    
    protected $table = 'produkty';

    public $timestamps = false;

    // Dodajemy 'is_wlasny' do fillable
    protected $fillable = ['id','tw_nazwa', 'tw_idabaco', 'is_wlasny'];

    // Relacja many-to-many z zamówieniami
    public function zamowienia()
    {
        return $this->belongsToMany(Zamowienie::class, 'produkt_zamowienie')
                    ->withPivot('ilosc');
    }

    // Relacja z kodami EAN (one-to-many)
    public function eanCodes()
    {
        return $this->hasMany(EanCode::class, 'produkt_id');
    }

    // Relacja z wsadami (through pivot table)
    public function wsady()
    {
        return $this->belongsToMany(Wsad::class, 'produkt_wsad')
                    ->withPivot('ilosc');
    }

    // Relacja ze stratami (through pivot table)
    public function straty()
    {
        return $this->belongsToMany(Strata::class, 'produkt_strata')
                    ->withPivot('ilosc');
    }

    // Dodatkowe metody pomocnicze
    public function isWlasny(): bool
    {
        return (bool) $this->is_wlasny;
    }

    public function isObcy(): bool
    {
        return !$this->isWlasny();
    }
}