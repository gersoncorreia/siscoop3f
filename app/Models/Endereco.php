<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    protected $fillable = [
        'associado_id',
        'tipo',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cep',
        'cidade',
        'uf',
        'principal',
    ];

    protected $casts = [
        'logradouro' => 'encrypted',
        'complemento' => 'encrypted',
        'principal' => 'boolean',
    ];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }
}
