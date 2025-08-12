<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZamowienieTemplate extends Model
{
    protected $table = 'zamowienie_template';  // nazwa tabeli, którą utworzysz w migracji
    public $timestamps = false;

    protected $fillable = ['nazwa']; // tylko nazwa, bo to dla zamówień niewłasnych (bez automat_id)
    

    public function produkty()
    {
        return $this->hasMany(ZamowienieTemplateProdukt::class, 'zamowienie_template_id');
    }
}
