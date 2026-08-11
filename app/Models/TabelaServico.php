<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TabelaServico extends Model
{
    use HasFactory;

    protected $table = 'tabela_servicos';

    public const CATEGORIA_CONSULTA = 'consulta';
    public const CATEGORIA_EXAME_LAB = 'exame_laboratorial';
    public const CATEGORIA_EXAME_IMG = 'exame_imagem';
    public const CATEGORIA_PROCEDIMENTO = 'procedimento';
    public const CATEGORIA_CIRURGIA = 'cirurgia';
    public const CATEGORIA_INTERNACAO = 'internacao';
    public const CATEGORIA_FISIOTERAPIA = 'fisioterapia';
    public const CATEGORIA_ODONTOLOGIA = 'odontologia';
    public const CATEGORIA_PRONTO_ATENDIMENTO = 'pronto_atendimento';
    public const CATEGORIA_OUTROS = 'outros';

    protected $fillable = [
        'codigo',
        'nome',
        'categoria',
        'descricao',
        'valor_base',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'valor_base' => 'decimal:2',
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    /**
     * All available categories with human-readable labels.
     */
    public static function categorias(): array
    {
        return [
            self::CATEGORIA_CONSULTA => 'Consulta',
            self::CATEGORIA_EXAME_LAB => 'Exame Laboratorial',
            self::CATEGORIA_EXAME_IMG => 'Exame de Imagem',
            self::CATEGORIA_PROCEDIMENTO => 'Procedimento',
            self::CATEGORIA_CIRURGIA => 'Cirurgia',
            self::CATEGORIA_INTERNACAO => 'Internação',
            self::CATEGORIA_FISIOTERAPIA => 'Fisioterapia',
            self::CATEGORIA_ODONTOLOGIA => 'Odontologia',
            self::CATEGORIA_PRONTO_ATENDIMENTO => 'Pronto Atendimento',
            self::CATEGORIA_OUTROS => 'Outros',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeAtivo(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeOrdenado(Builder $query): Builder
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    // ─── Relationships ────────────────────────────────────────

    public function precosPlanos()
    {
        return $this->hasMany(PlanoServicoPreco::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Get the effective price for a given plan.
     * Returns the custom price, discounted price, or base price.
     */
    public function getPrecoParaPlano(?PlanoAdesao $plano): float
    {
        if (!$plano) {
            return (float) $this->valor_base;
        }

        $pivot = $this->precosPlanos()->where('plano_adesao_id', $plano->id)->first();

        if (!$pivot) {
            return (float) $this->valor_base;
        }

        if ($pivot->incluso) {
            return 0.0;
        }

        if ($pivot->valor_personalizado !== null) {
            return (float) $pivot->valor_personalizado;
        }

        if ($pivot->desconto_percentual !== null) {
            return round($this->valor_base * (1 - $pivot->desconto_percentual / 100), 2);
        }

        return (float) $this->valor_base;
    }

    /**
     * Get the human-readable category label.
     */
    public function getCategoriaLabelAttribute(): string
    {
        return self::categorias()[$this->categoria] ?? $this->categoria;
    }
}
