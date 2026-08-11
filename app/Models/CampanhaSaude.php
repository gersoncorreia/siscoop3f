<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CampanhaSaude extends Model
{
    use HasFactory;

    protected $table = 'campanhas_saude';

    public const TIPO_VACINACAO = 'vacinacao';
    public const TIPO_CHECKUP = 'checkup';
    public const TIPO_PREVENCAO = 'prevencao';
    public const TIPO_PROMOCAO = 'promocao';
    public const TIPO_EDUCATIVA = 'educativa';
    public const TIPO_OUTRO = 'outro';

    public const PUBLICO_TODOS = 'todos';
    public const PUBLICO_INDIVIDUAL = 'individual';
    public const PUBLICO_FAMILIAR = 'familiar';
    public const PUBLICO_EMPRESARIAL = 'empresarial';
    public const PUBLICO_INSTITUCIONAL = 'institucional';
    public const PUBLICO_IDOSOS = 'idosos';
    public const PUBLICO_CRIANCAS = 'criancas';
    public const PUBLICO_GESTANTES = 'gestantes';

    protected $fillable = [
        'titulo',
        'descricao',
        'tipo',
        'publico_alvo',
        'data_inicio',
        'data_fim',
        'ativo',
        'imagem_path',
        'meta_participantes',
        'total_participantes',
        'notificar_membros',
        'meta_dados',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
        'meta_participantes' => 'integer',
        'total_participantes' => 'integer',
        'notificar_membros' => 'boolean',
        'meta_dados' => 'array',
    ];

    /**
     * Available campaign types with labels.
     */
    public static function tipos(): array
    {
        return [
            self::TIPO_VACINACAO => 'Vacinação',
            self::TIPO_CHECKUP => 'Check-up Preventivo',
            self::TIPO_PREVENCAO => 'Prevenção',
            self::TIPO_PROMOCAO => 'Promoção da Saúde',
            self::TIPO_EDUCATIVA => 'Educativa',
            self::TIPO_OUTRO => 'Outro',
        ];
    }

    /**
     * Available target audiences with labels.
     */
    public static function publicosAlvo(): array
    {
        return [
            self::PUBLICO_TODOS => 'Todos os Membros',
            self::PUBLICO_INDIVIDUAL => 'Plano Individual',
            self::PUBLICO_FAMILIAR => 'Plano Familiar',
            self::PUBLICO_EMPRESARIAL => 'Plano Empresarial',
            self::PUBLICO_INSTITUCIONAL => 'Plano Institucional',
            self::PUBLICO_IDOSOS => 'Idosos (60+)',
            self::PUBLICO_CRIANCAS => 'Crianças (0-12)',
            self::PUBLICO_GESTANTES => 'Gestantes',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeVigentes(Builder $query): Builder
    {
        return $query->where('data_inicio', '<=', now())
            ->where(function ($q) {
                $q->whereNull('data_fim')->orWhere('data_fim', '>=', now());
            });
    }

    public function scopeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Check if the campaign is currently active and within date range.
     */
    public function isVigente(): bool
    {
        if (!$this->ativo) {
            return false;
        }

        $now = now()->startOfDay();

        if ($this->data_inicio > $now) {
            return false;
        }

        if ($this->data_fim && $this->data_fim < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get progress percentage towards participant goal.
     */
    public function getProgressoAttribute(): ?float
    {
        if (!$this->meta_participantes || $this->meta_participantes === 0) {
            return null;
        }

        return min(100, round(($this->total_participantes / $this->meta_participantes) * 100, 1));
    }

    /**
     * Get the human-readable type label.
     */
    public function getTipoLabelAttribute(): string
    {
        return self::tipos()[$this->tipo] ?? $this->tipo;
    }

    /**
     * Get the human-readable audience label.
     */
    public function getPublicoAlvoLabelAttribute(): string
    {
        return self::publicosAlvo()[$this->publico_alvo] ?? $this->publico_alvo;
    }
}
