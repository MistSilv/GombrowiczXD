<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wsad extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_wsadu',
        'automat_id',
    ];

    public function automat()
    {
        return $this->belongsTo(Automat::class);
    }

    public function produkty()
    {
        return $this->hasMany(ProduktWsad::class);
    }

    protected $table = 'wsady';

    /*
    protected $casts = [
        'data_wsadu' => 'datetime',
    ];
    */
}
