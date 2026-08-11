<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Dependente extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'associado_id',
        'nome',
        'cpf',
        'data_nascimento',
        'parentesco',
        'tipo_dependencia',
        'autoriza_acesso',
        'email',
        'user_id',
        'status',
    ];
    
    protected $casts = [
        'cpf' => \App\Casts\EncryptedOrPlain::class,
        'email' => \App\Casts\EncryptedOrPlain::class,
        'data_nascimento' => 'date',
        'autoriza_acesso' => 'boolean',
    ];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    /**
     * Retorna o valor da taxa de adesão baseado no tipo de dependência.
     * Dependentes não legais pagam o mesmo valor que o titular (R$ 100,00).
     * Dependentes legais pagam taxa reduzida (R$ 80,00).
     */
    public function getTaxaAdesao(): float
    {
        return $this->tipo_dependencia === 'nao_legal' ? 100.00 : 80.00;
    }
}
