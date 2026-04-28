<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanoSaude extends Model
{
    protected $fillable = ['nome', 'coberturas', 'faixas_etarias'];

    protected $casts = [
        'coberturas' => 'array',
        'faixas_etarias' => 'array',
    ];
}
