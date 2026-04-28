<?php

namespace Tests\Feature\Integration;

use Tests\TenantTestCase;
use App\Models\Associado;
use App\Models\CotaCapital;
use App\Models\LancamentoFinanceiro;
use App\Models\Reembolso;
use App\Models\Dependente;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CooperadoPortalTest extends TenantTestCase
{
    protected $cooperadoUser;
    protected $associado;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        Role::findOrCreate('Associado', 'web');
        Role::findOrCreate('Administrador Cooperativa', 'web');

        // Create cooperado user with linked associado
        $this->cooperadoUser = User::create([
            'name' => 'Maria Cooperada',
            'email' => 'maria@teste.com',
            'password' => bcrypt('senha123'),
            'accepted_lgpd_at' => now(),
        ]);
        $this->cooperadoUser->assignRole('Associado');

        $this->associado = Associado::create([
            'user_id' => $this->cooperadoUser->id,
            'nome' => 'Maria Cooperada',
            'cpf' => '123.456.789-01',
            'email' => 'maria@teste.com',
            'status' => 'ativo',
            'data_adesao' => now(),
        ]);

        // Seed some test data
        CotaCapital::create([
            'associado_id' => $this->associado->id,
            'valor_quota' => 100.00,
            'qtd_quotas' => 2,
            'saldo' => 200.00,
            'tipo_movimento' => 'integralizacao_inicial',
        ]);

        LancamentoFinanceiro::create([
            'associado_id' => $this->associado->id,
            'tipo' => 'saida',
            'valor' => 150.00,
            'data_vencimento' => now()->format('Y-m-d'),
            'status' => 'pendente',
        ]);

        Dependente::create([
            'associado_id' => $this->associado->id,
            'nome' => 'Joãozinho',
            'parentesco' => 'Filho',
            'status' => 'ativo',
        ]);
    }

    public function test_cooperado_can_access_dashboard(): void
    {
        $response = $this->tenantGet('/cooperado', $this->cooperadoUser);
        $response->assertStatus(200);
    }

    public function test_cooperado_dashboard_returns_correct_stats(): void
    {
        $response = $this->tenantGet('/cooperado', $this->cooperadoUser);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Cooperado/Dashboard')
                ->has('associado')
                ->has('stats')
                ->where('stats.saldoCotaCapital', 200.00)
                ->where('stats.mensalidadesPendentes', 1)
                ->where('stats.totalDependentes', 1)
        );
    }

    public function test_cooperado_can_access_financeiro(): void
    {
        $response = $this->tenantGet('/cooperado/financeiro', $this->cooperadoUser);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Cooperado/Financeiro')
                ->has('lancamentos.data', 1)
        );
    }

    public function test_cooperado_can_access_cotas(): void
    {
        $response = $this->tenantGet('/cooperado/cotas', $this->cooperadoUser);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Cooperado/Cotas')
                ->where('saldoTotal', 200.00)
                ->has('cotas', 1)
        );
    }

    public function test_cooperado_can_access_reembolsos(): void
    {
        $response = $this->tenantGet('/cooperado/reembolsos', $this->cooperadoUser);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Cooperado/Reembolsos')
                ->has('reembolsos', 0)
        );
    }

    public function test_cooperado_can_submit_reembolso(): void
    {
        $response = $this->tenantPost('/cooperado/reembolsos', [
            'valor_solicitado' => 75.50,
            'descricao' => 'Consulta médica',
        ], $this->cooperadoUser);

        $response->assertRedirect();

        $this->assertDatabaseHas('reembolsos', [
            'associado_id' => $this->associado->id,
            'valor_solicitado' => 75.50,
            'status' => 'pendente',
        ]);
    }

    public function test_cooperado_can_access_dependentes(): void
    {
        $response = $this->tenantGet('/cooperado/dependentes', $this->cooperadoUser);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Cooperado/Dependentes')
                ->has('dependentes', 1)
        );
    }

    public function test_cooperado_can_access_documentos(): void
    {
        $response = $this->tenantGet('/cooperado/documentos', $this->cooperadoUser);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Cooperado/Documentos')
        );
    }

    public function test_cooperado_data_isolation(): void
    {
        // Create another cooperado
        $otherUser = User::create([
            'name' => 'Carlos Outro',
            'email' => 'carlos@teste.com',
            'password' => bcrypt('senha123'),
            'accepted_lgpd_at' => now(),
        ]);
        $otherUser->assignRole('Associado');

        $otherAssociado = Associado::create([
            'user_id' => $otherUser->id,
            'nome' => 'Carlos Outro',
            'cpf' => '987.654.321-00',
            'email' => 'carlos@teste.com',
            'status' => 'ativo',
            'data_adesao' => now(),
        ]);

        LancamentoFinanceiro::create([
            'associado_id' => $otherAssociado->id,
            'tipo' => 'saida',
            'valor' => 999.99,
            'data_vencimento' => now()->format('Y-m-d'),
            'status' => 'pendente',
        ]);

        // Maria should only see her own data
        $response = $this->tenantGet('/cooperado/financeiro', $this->cooperadoUser);
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Cooperado/Financeiro')
                ->has('lancamentos.data', 1) // Only Maria's lancamento, not Carlos's
        );
    }
}
