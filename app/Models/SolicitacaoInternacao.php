<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoInternacao extends Model
{
    use HasFactory;

    protected $table = 'solicitacoes_internacao';

    protected $fillable = [
        'user_id',
        'hospital_clinica',
        'procedimento_principal',
        'data_prevista',
        'crm_medico',
        'nome_medico',
        'justificativa_clinica',
        'anexo_guia_path',
        'status',
        'motivo_rejeicao',
    ];

    protected $casts = [
        'data_prevista' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
