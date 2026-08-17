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
        'forma_pagamento_adesao',
        'autoriza_desconto_folha',
        // Carteirinha Digital (Fundação)
        'carteirinha_numero',
        'carteirinha_tipo',
        'plano_adesao_id',
        'carteirinha_validade',
        'carteirinha_status',
        'carteirinha_emitida_em',
        // Auditoria de Aprovação
        'aprovado_por_user_id',
        'aprovado_por_nome',
        'aprovado_em',
        'forma_pagamento_aprovacao',
        'observacoes_aprovacao',
        'parcelas_aprovacao',
        'comprovante_pix_path',
        'detalhes_financeiro_aprovacao',
    ];

    protected $casts = [
        'cpf' => \App\Casts\EncryptedOrPlain::class,
        'email' => \App\Casts\EncryptedOrPlain::class,
        'nascimento' => 'date',
        'data_adesao' => 'date',
        'carteirinha_validade' => 'date',
        'carteirinha_emitida_em' => 'datetime',
        'aprovado_em' => 'datetime',
        'detalhes_financeiro_aprovacao' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovado_por_user_id');
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

    public function planoAdesao()
    {
        return $this->belongsTo(PlanoAdesao::class, 'plano_adesao_id');
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

    /**
     * Generate a unique card number for this member.
     * Format: YYYY-NNNNN (year + sequential 5-digit number)
     */
    public function gerarNumeroCarteirinha(): string
    {
        $prefix = date('Y');
        $lastNumber = static::where('carteirinha_numero', 'like', $prefix . '-%')
            ->orderByRaw("CAST(SUBSTRING(carteirinha_numero, 6) AS INTEGER) DESC")
            ->value('carteirinha_numero');

        $nextSeq = 1;
        if ($lastNumber) {
            $parts = explode('-', $lastNumber);
            $nextSeq = ((int) ($parts[1] ?? 0)) + 1;
        }

        return $prefix . '-' . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Check if the digital card is currently valid.
     */
    public function isCarteirinhaValida(): bool
    {
        if ($this->carteirinha_status !== 'ativa') {
            return false;
        }

        if ($this->carteirinha_validade && $this->carteirinha_validade->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Emit the digital card for this member.
     */
    public function emitirCarteirinha(string $tipo = 'individual', ?int $planoId = null): void
    {
        $this->update([
            'carteirinha_numero' => $this->carteirinha_numero ?: $this->gerarNumeroCarteirinha(),
            'carteirinha_tipo' => $tipo,
            'plano_adesao_id' => $planoId,
            'carteirinha_validade' => now()->addYear(),
            'carteirinha_status' => 'ativa',
            'carteirinha_emitida_em' => now(),
        ]);
    }
}
