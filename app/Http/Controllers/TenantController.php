<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use Illuminate\Support\Facades\Redirect;

class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function store(StoreTenantRequest $request)
    {
        $validated = $request->validated();

        $result = $this->tenantService->createTenant($validated);

        $tenant = $result['tenant'];
        $tempPassword = $result['temp_password'];
        $domain = $tenant->domains()->first()?->domain ?? 'N/A';
        $tipoLabel = $tenant->tipo === 'fundacao' ? 'Fundação' : 'Cooperativa';

        $message = "{$tipoLabel} '{$tenant->nome}' criada com sucesso!"
            . "\n\n📌 Credenciais de Acesso Inicial:"
            . "\n🌐 Domínio: {$domain}"
            . "\n👤 Email: {$validated['admin_email']}"
            . "\n🔑 Senha temporária: {$tempPassword}"
            . "\n\n⚠️ A senha deverá ser alterada no primeiro login.";

        return Redirect::route('master-admin.dashboard')->with('success', $message);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $validated = $request->validated();

        $this->tenantService->updateTenant($tenant, $validated);

        $tipoLabel = $tenant->tipo === 'fundacao' ? 'Fundação' : 'Cooperativa';
        return Redirect::route('master-admin.dashboard')->with('success', "{$tipoLabel} atualizada com sucesso!");
    }

    public function destroy(Tenant $tenant)
    {
        $this->tenantService->deleteTenant($tenant);
        $tipoLabel = $tenant->tipo === 'fundacao' ? 'Fundação' : 'Cooperativa';
        return Redirect::route('master-admin.dashboard')->with('success', "{$tipoLabel} desativada com sucesso!");
    }

    public function restore($id)
    {
        $tenant = $this->tenantService->restoreTenant($id);
        $tipoLabel = $tenant->tipo === 'fundacao' ? 'Fundação' : 'Cooperativa';
        return Redirect::route('master-admin.dashboard')->with('success', "{$tipoLabel} reativada com sucesso!");
    }

    public function forceDelete($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tipoLabel = $tenant->tipo === 'fundacao' ? 'Fundação' : 'Cooperativa';
        $this->tenantService->forceDeleteTenant($id);

        return Redirect::route('master-admin.dashboard')->with('success', "{$tipoLabel} excluída definitivamente!");
    }

    public function export($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        
        // TODO: Implement actual data export logic (JSON/SQL payload via temporary URL)
        // For now, return a placeholder JSON
        return response()->json([
            'message' => 'Exportação da base LGPD solicitada',
            'tenant' => $tenant->toArray(),
        ]);
    }
}
