<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WsadTemplateProdukt extends Model
{
    //
    protected $table = 'wsad_template_produkt';
    public $timestamps = false;

    protected $fillable = ['wsad_template_id', 'produkt_id', 'ilosc'];

    public function produkt()
    {
        return $this->belongsTo(Produkt::class);
    }


}
