<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaturaPrestador extends Model
{
    protected $table = 'faturas_prestador';

    protected $fillable = [
        'prestador_id',
        'mes_referencia',
        'valor_total',
        'arquivo_guia',
        'status',
        'data_pagamento'
    ];

    public function prestador()
    {
        return $this->belongsTo(Prestador::class);
    }

    public function solicitacoes()
    {
        return $this->hasMany(SolicitacaoServico::class, 'fatura_id');
    }
}
