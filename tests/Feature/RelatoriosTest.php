<?php

use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RelatorioAssociadosExport;
use App\Exports\RelatorioFinanceiroExport;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $tenantService = new TenantService();
    $tenantData = $tenantService->createTenant([
        'nome' => 'Cooperativa de Teste ' . \Illuminate\Support\Str::random(5),
        'ramo' => 'saude'
    ]);
    $this->tenant = $tenantData['tenant'];
    
    tenancy()->initialize($this->tenant);
    $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    
    // Usuário sem permissões de relatório
    $this->userComum = User::factory()->create([
        'accepted_lgpd_at' => now(),
        'has_temporary_password' => false
    ]);
    $this->userComum->assignRole('Associado');
    
    // Usuário da Auditoria (com permissão)
    $this->userAuditoria = User::factory()->create([
        'accepted_lgpd_at' => now(),
        'has_temporary_password' => false
    ]);
    $this->userAuditoria->assignRole('Auditoria');
    
    // Usuário Contábil
    $this->userContabil = User::factory()->create([
        'accepted_lgpd_at' => now(),
        'has_temporary_password' => false
    ]);
    $this->userContabil->assignRole('Contábil');
});

it('bloqueia o acesso a rota de relatorios para usuarios sem permissao', function () {
    $baseUrl = 'http://' . $this->tenant->domains->first()->domain;
    $response = $this->actingAs($this->userComum)->get($baseUrl . '/relatorios');
    
    $response->assertStatus(403);
});

it('permite acesso a rota de relatorios para usuarios com permissao de auditoria', function () {
    $baseUrl = 'http://' . $this->tenant->domains->first()->domain;
    // Auditores têm a permissão 'relatorios.view'
    $response = $this->actingAs($this->userAuditoria)->get($baseUrl . '/relatorios');
    
    $response->assertStatus(200);
});

it('faz o download do relatorio de associados em excel', function () {
    Excel::fake();
    $now = \Illuminate\Support\Carbon::parse('2026-06-07 12:00:00');
    \Illuminate\Support\Carbon::setTestNow($now);
    
    $baseUrl = 'http://' . $this->tenant->domains->first()->domain;
    
    $response = $this->actingAs($this->userAuditoria)->get($baseUrl . '/relatorios/export/associados');
    
    $response->assertStatus(200);
    $expectedFilename = 'associados_' . $now->format('Y-m-d_His') . '.xlsx';
    Excel::assertDownloaded($expectedFilename, function (RelatorioAssociadosExport $export) {
        return true;
    });
});

it('permite o usuario contabil baixar relatorios financeiros', function () {
    Excel::fake();
    $now = \Illuminate\Support\Carbon::parse('2026-06-07 12:00:00');
    \Illuminate\Support\Carbon::setTestNow($now);
    
    $baseUrl = 'http://' . $this->tenant->domains->first()->domain;
    
    // O usuário contábil tem 'contabil.export', que libera a exportação financeira
    $response = $this->actingAs($this->userContabil)->get($baseUrl . '/relatorios/export/financeiro');
    
    $response->assertStatus(200);
    $expectedFilename = 'financeiro_' . $now->format('Y-m-d_His') . '.xlsx';
    Excel::assertDownloaded($expectedFilename, function (RelatorioFinanceiroExport $export) {
        return true;
    });
});
