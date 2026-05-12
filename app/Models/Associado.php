<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Associado extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'user_id',
        'nome',
        'cpf',
        'email',
        'nascimento',
        'status',
        'matricula',
        'data_adesao',
    ];

    protected $casts = [
        'cpf' => 'encrypted',
        'email' => 'encrypted',
        // 'endereco' removed
        'nascimento' => 'date',
        'data_adesao' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dependentes()
    {
        return $this->hasMany(Dependente::class);
    }

    public function enderecos()
    {
        return $this->hasMany(Endereco::class);
    }

    public function telefones()
    {
        return $this->hasMany(Telefone::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function cotasCapital()
    {
        return $this->hasMany(CotaCapital::class);
    }

    public function lancamentosFinanceiros()
    {
        return $this->hasMany(LancamentoFinanceiro::class);
    }

    public function reembolsos()
    {
        return $this->hasMany(Reembolso::class);
    }

    /**
     * Verifica se o cooperado preencheu as informações base necessárias (data de nascimento e endereço).
     */
    public function isCadastroCompleto(): bool
    {
        return $this->nascimento !== null 
            && $this->enderecos()->exists() 
            && $this->telefones()->exists();
    }
}
