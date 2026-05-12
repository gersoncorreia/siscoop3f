<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitacaoServico extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'solicitacoes_servicos';

    protected $fillable = [
        'associado_id',
        'prestador_id',
        'servico_id',
        'fatura_id',
        'observacoes',
        'status',
        'motivo_recusa',
        'valor_cobrado',
        'data_realizacao'
    ];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }

    public function prestador()
    {
        return $this->belongsTo(Prestador::class);
    }

    public function servico()
    {
        return $this->belongsTo(PrestadorServico::class);
    }

    public function fatura()
    {
        return $this->belongsTo(FaturaPrestador::class, 'fatura_id');
    }
}
