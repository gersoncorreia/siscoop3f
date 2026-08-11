<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanoServicoPreco extends Model
{
    use HasFactory;

    protected $table = 'plano_servico_precos';

    protected $fillable = [
        'plano_adesao_id',
        'tabela_servico_id',
        'valor_personalizado',
        'desconto_percentual',
        'incluso',
    ];

    protected $casts = [
        'valor_personalizado' => 'decimal:2',
        'desconto_percentual' => 'decimal:2',
        'incluso' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function plano()
    {
        return $this->belongsTo(PlanoAdesao::class, 'plano_adesao_id');
    }

    public function servico()
    {
        return $this->belongsTo(TabelaServico::class, 'tabela_servico_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Calculate the effective price for this plan-service combination.
     */
    public function getPrecoEfetivoAttribute(): float
    {
        if ($this->incluso) {
            return 0.0;
        }

        if ($this->valor_personalizado !== null) {
            return (float) $this->valor_personalizado;
        }

        if ($this->desconto_percentual !== null && $this->servico) {
            return round($this->servico->valor_base * (1 - $this->desconto_percentual / 100), 2);
        }

        return (float) ($this->servico?->valor_base ?? 0);
    }
}
