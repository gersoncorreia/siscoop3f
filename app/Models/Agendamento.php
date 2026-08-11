<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Agendamento extends Model
{
    use HasFactory;

    // ─── Type Constants ───────────────────────────────────────
    public const TIPO_CONSULTA = 'consulta';
    public const TIPO_EXAME = 'exame';
    public const TIPO_PRONTO_ATENDIMENTO = 'pronto_atendimento';

    // ─── Status Constants ─────────────────────────────────────
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_CONFIRMADO = 'confirmado';
    public const STATUS_REALIZADO = 'realizado';
    public const STATUS_CANCELADO = 'cancelado';

    // ─── Modality Constants ───────────────────────────────────
    public const MODALIDADE_PRESENCIAL = 'presencial';
    public const MODALIDADE_TELEMEDICINA = 'telemedicina';

    protected $fillable = [
        'user_id',
        'tipo',
        'especialidade',
        'prestador_id',
        'tabela_servico_id',
        'valor_cobrado',
        'modalidade',
        'data_hora',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'valor_cobrado' => 'decimal:2',
    ];

    /**
     * Available types with labels.
     */
    public static function tipos(): array
    {
        return [
            self::TIPO_CONSULTA => 'Consulta',
            self::TIPO_EXAME => 'Exame',
            self::TIPO_PRONTO_ATENDIMENTO => 'Pronto Atendimento',
        ];
    }

    /**
     * Available modalities with labels.
     */
    public static function modalidades(): array
    {
        return [
            self::MODALIDADE_PRESENCIAL => 'Presencial',
            self::MODALIDADE_TELEMEDICINA => 'Telemedicina',
        ];
    }

    // ─── Relationships ────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prestador()
    {
        return $this->belongsTo(Prestador::class);
    }

    public function tabelaServico()
    {
        return $this->belongsTo(TabelaServico::class);
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeHoje(Builder $query): Builder
    {
        return $query->whereDate('data_hora', today());
    }

    public function scopeMesAtual(Builder $query): Builder
    {
        return $query->whereMonth('data_hora', now()->month)
            ->whereYear('data_hora', now()->year);
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDENTE);
    }

    public function scopeRealizados(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REALIZADO);
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function isProntoAtendimento(): bool
    {
        return $this->tipo === self::TIPO_PRONTO_ATENDIMENTO;
    }

    public function isTelemedicina(): bool
    {
        return $this->modalidade === self::MODALIDADE_TELEMEDICINA;
    }
}
