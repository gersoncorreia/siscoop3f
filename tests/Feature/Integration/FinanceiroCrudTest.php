<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use App\Models\LancamentoFinanceiro;
use Tests\TenantTestCase;

class FinanceiroCrudTest extends TenantTestCase
{
    /** @test */
    public function test_can_create_lancamento_with_valid_data()
    {
        $user = $this->createTenantUser();

        $response = $this->tenantPost('/financeiro/lancamentos', [
            'tipo' => 'receita',
            'valor' => 1500.00,
            'data_vencimento' => '2026-04-15',
            'status' => 'pendente',
        ], $user);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lancamento_financeiros', [
            'tipo' => 'receita',
            'valor' => 1500.00,
            'status' => 'pendente',
        ]);
    }

    /** @test */
    public function test_cannot_create_lancamento_with_negative_value()
    {
        $user = $this->createTenantUser();

        $response = $this->tenantPost('/financeiro/lancamentos', [
            'tipo' => 'receita',
            'valor' => -100.00,
            'data_vencimento' => '2026-04-15',
            'status' => 'pendente',
        ], $user);

        $response->assertSessionHasErrors(['valor']);
    }

    /** @test */
    public function test_cannot_create_lancamento_with_invalid_tipo()
    {
        $user = $this->createTenantUser();

        $response = $this->tenantPost('/financeiro/lancamentos', [
            'tipo' => 'hack_injection',
            'valor' => 100.00,
            'data_vencimento' => '2026-04-15',
            'status' => 'pendente',
        ], $user);

        $response->assertSessionHasErrors(['tipo']);
    }

    /** @test */
    public function test_cannot_create_lancamento_without_required()
    {
        $user = $this->createTenantUser();

        $response = $this->tenantPost('/financeiro/lancamentos', [], $user);

        $response->assertSessionHasErrors(['tipo', 'valor', 'data_vencimento', 'status']);
    }

    /** @test */
    public function test_can_delete_lancamento()
    {
        $user = $this->createTenantUser();

        $lancamento = LancamentoFinanceiro::create([
            'tipo' => 'despesa',
            'valor' => 200.00,
            'data_vencimento' => '2026-05-01',
            'status' => 'pago',
        ]);

        $response = $this->tenantDelete("/financeiro/lancamentos/{$lancamento->id}", $user);

        $response->assertRedirect();
        $this->assertDatabaseMissing('lancamento_financeiros', ['id' => $lancamento->id]);
    }
}
