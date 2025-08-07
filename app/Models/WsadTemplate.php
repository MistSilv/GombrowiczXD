<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WsadTemplate extends Model
{
    protected $table = 'wsad_template';
    public $timestamps = false;

    protected $fillable = ['automat_id', 'nazwa'];

    protected static function boot()
    {
        parent::boot();

    }

    public function produkty()
    {
        return $this->hasMany(WsadTemplateProdukt::class, 'wsad_template_id');
    }



    public function automat()
    {
        return $this->belongsTo(Automat::class);
    }
}
