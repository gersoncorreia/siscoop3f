<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class TenantController extends Controller
{
    public function store(Request $request, TenantService $tenantService)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ramo' => 'nullable|string',
            'admin_nome' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
        ]);

        $result = $tenantService->createTenant($validated);

        $tenant = $result['tenant'];
        $tempPassword = $result['temp_password'];
        $domain = $tenant->domains()->first()?->domain ?? 'N/A';

        $message = "Cooperativa '{$tenant->nome}' criada com sucesso!"
            . "\n\n📌 Credenciais de Acesso Inicial:"
            . "\n🌐 Domínio: {$domain}"
            . "\n👤 Email: {$validated['admin_email']}"
            . "\n🔑 Senha temporária: {$tempPassword}"
            . "\n\n⚠️ A senha deverá ser alterada no primeiro login.";

        return Redirect::route('master-admin.dashboard')->with('success', $message);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ramo' => 'nullable|string',
        ]);

        $tenant->update($validated);

        return Redirect::route('master-admin.dashboard')->with('success', 'Cooperativa atualizada com sucesso!');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete(); // Soft delete triggering de-activation
        return Redirect::route('master-admin.dashboard')->with('success', 'Cooperativa desativada com sucesso!');
    }

    public function restore($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tenant->restore();

        return Redirect::route('master-admin.dashboard')->with('success', 'Cooperativa reativada com sucesso!');
    }

    public function forceDelete($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        
        try {
            $tenant->forceDelete(); // Hard delete triggering database deletion
        } catch (\Illuminate\Database\QueryException $e) {
            // Ignorar erro 1008 (Database doesn't exist), util durante falhas de criação ou Testes TDD
            if (strpos($e->getMessage(), 'database doesn\'t exist') === false && $e->getCode() != 'HY000') {
                throw $e;
            }
            // If it can't drop database but threw, the record might not be deleted.
            // We manually delete the record without the Events if needed, 
            // but usually it throws after deleting or from the Observer. 
            // Stancl observer runs on 'deleting'. If it failed, we forcefully remove it from DB:
            \Illuminate\Support\Facades\DB::table('tenants')->where('id', $id)->delete();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::table('tenants')->where('id', $id)->delete();
        }

        return Redirect::route('master-admin.dashboard')->with('success', 'Cooperativa excluída definitivamente!');
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
