<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telefone extends Model
{
    protected $fillable = [
        'associado_id',
        'tipo',
        'numero',
        'principal',
    ];

    protected $casts = [
        'numero' => 'encrypted',
        'principal' => 'boolean',
    ];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }
}
