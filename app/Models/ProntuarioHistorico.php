<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProntuarioHistorico extends Model
{
    use HasFactory;

    protected $table = 'prontuario_historico';

    protected $fillable = [
        'user_id',
        'medico_nome',
        'crm',
        'data_atendimento',
        'diagnostico_principal',
        'prescricao',
        'observacoes_clinicas',
    ];

    protected $casts = [
        'data_atendimento' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
