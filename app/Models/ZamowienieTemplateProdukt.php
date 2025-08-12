<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZamowienieTemplateProdukt extends Model
{
    protected $table = 'zamowienie_template_produkt'; // tabela związkowa
    public $timestamps = false;

    protected $fillable = ['zamowienie_template_id', 'produkt_id', 'ilosc'];

    public function produkt()
    {
        return $this->belongsTo(Produkt::class);
    }

    public function zamowienieTemplate()
    {
        return $this->belongsTo(ZamowienieTemplate::class, 'zamowienie_template_id');
    }
}
