<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestadorServico extends Model
{
    protected $fillable = [
        'prestador_id',
        'nome',
        'descricao',
        'valor_acordado',
        'ativo'
    ];

    public function prestador()
    {
        return $this->belongsTo(Prestador::class);
    }
}
