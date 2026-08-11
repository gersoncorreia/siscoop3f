<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, SoftDeletes;

    const TIPO_COOPERATIVA = 'cooperativa';
    const TIPO_FUNDACAO = 'fundacao';

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'tipo',
            'servicos',
            'possui_landing_page',
            'deleted_at',
        ];
    }

    public function isFundacao(): bool
    {
        return $this->tipo === self::TIPO_FUNDACAO;
    }

    public function isCooperativa(): bool
    {
        return $this->tipo === self::TIPO_COOPERATIVA;
    }

    /**
     * Check if landing page is enabled for this tenant.
     */
    public function hasLandingPage(): bool
    {
        // Se nulo ou não definido, padrão é true por compatibilidade
        return $this->possui_landing_page !== false && $this->possui_landing_page !== 0 && $this->possui_landing_page !== '0';
    }

    /**
     * Check if a specific service module is enabled for this tenant.
     */
    public function hasServico(string $servico): bool
    {
        $servicos = $this->servicos;
        // Cooperativas sem campo servicos têm acesso total por compatibilidade
        if ($servicos === null && $this->isCooperativa()) {
            return true;
        }
        return in_array($servico, (array) $servicos, true);
    }

    public function contratosComoContratante()
    {
        return $this->hasMany(ContratoEntidade::class, 'tenant_contratante_id');
    }

    public function contratosComoPrestador()
    {
        return $this->hasMany(ContratoEntidade::class, 'tenant_prestador_id');
    }

    /**
     * Obter ids dos tenants prestadores contratados por esta entidade
     */
    public function idsPrestadoresContratados(): array
    {
        return $this->contratosComoContratante()
            ->where('status', 'ativo')
            ->pluck('tenant_prestador_id')
            ->toArray();
    }

    protected $casts = [
        'payment_config' => \App\Casts\EncryptedOrPlainArray::class,
        'servicos' => 'array',
        'possui_landing_page' => 'boolean',
    ];
}
