<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $fillable = [
        'associado_id',
        'dependente_id',
        'titulo',
        'tipo',
        'arquivo_path',
        'status',
        'motivo_rejeicao'
    ];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }

    public function dependente()
    {
        return $this->belongsTo(Dependente::class);
    }
}
