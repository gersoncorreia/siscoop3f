<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reembolso extends Model
{
    protected $fillable = ['associado_id', 'valor_solicitado', 'status', 'comprovantes', 'descricao', 'motivo_recusa'];

    protected $casts = [
        'comprovantes' => 'array',
    ];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }

    public function coparticipacoes()
    {
        return $this->hasMany(Coparticipacao::class);
    }
}
