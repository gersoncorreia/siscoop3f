<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoEntidade extends Model
{
    protected $connection = 'central';
    protected $table = 'contratos_entidade';

    protected $fillable = [
        'tenant_contratante_id',
        'tenant_prestador_id',
        'status',
        'data_inicio',
        'data_fim',
        'descricao',
        'arquivos',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'arquivos' => 'array',
    ];

    public function contratante()
    {
        return $this->belongsTo(Tenant::class, 'tenant_contratante_id');
    }

    public function prestador()
    {
        return $this->belongsTo(Tenant::class, 'tenant_prestador_id');
    }
}
