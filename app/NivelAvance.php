<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NivelAvance extends Model
{
    //

    protected $table = 'nivel_avance';
    protected $primaryKey = 'nivel_avance_id';
    protected $fillable = [
        'escalamiento', 'product_min_viable','product_market_fit'
    ];
}
