<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PlanoAdesao extends Model
{
    use HasFactory;

    protected $table = 'planos_adesao';

    public const TIPO_INDIVIDUAL = 'individual';
    public const TIPO_FAMILIAR = 'familiar';
    public const TIPO_EMPRESARIAL = 'empresarial';
    public const TIPO_INSTITUCIONAL = 'institucional';

    protected $fillable = [
        'nome',
        'tipo',
        'valor_adesao',
        'valor_mensalidade',
        'descricao',
        'max_dependentes',
        'min_membros',
        'beneficios',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'valor_adesao' => 'decimal:2',
        'valor_mensalidade' => 'decimal:2',
        'max_dependentes' => 'integer',
        'min_membros' => 'integer',
        'beneficios' => 'array',
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * All available plan types with labels.
     */
    public static function tipos(): array
    {
        return [
            self::TIPO_INDIVIDUAL => 'Individual (Pessoa Física)',
            self::TIPO_FAMILIAR => 'Familiar',
            self::TIPO_EMPRESARIAL => 'Empresarial',
            self::TIPO_INSTITUCIONAL => 'Institucional',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeOrdenado(Builder $query): Builder
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    // ─── Relationships ────────────────────────────────────────

    public function precosServicos()
    {
        return $this->hasMany(PlanoServicoPreco::class);
    }

    public function associados()
    {
        return $this->hasMany(Associado::class, 'plano_adesao_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Get the human-readable type label.
     */
    public function getTipoLabelAttribute(): string
    {
        return self::tipos()[$this->tipo] ?? $this->tipo;
    }

    /**
     * Check if this plan allows dependents.
     */
    public function permiteDependentes(): bool
    {
        return in_array($this->tipo, [self::TIPO_FAMILIAR, self::TIPO_EMPRESARIAL, self::TIPO_INSTITUCIONAL]);
    }

    /**
     * Get the count of active members on this plan.
     */
    public function totalMembrosAtivos(): int
    {
        return $this->associados()->where('status', 'ativo')->count();
    }
}
