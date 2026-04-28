<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new tenant with a default domain
     */
    public function createTenant(array $data): Tenant
    {
        $tenantId = Str::slug($data['nome']);

        $tenant = Tenant::create([
            'id' => $tenantId,
            'nome' => $data['nome'],
            'ramo' => $data['ramo'] ?? 'geral',
        ]);

        $tenant->domains()->create([
            'domain' => $tenantId . '.' . parse_url(config('app.url', 'localhost'), PHP_URL_HOST),
        ]);

        return $tenant;
    }

    /**
     * Get specific tenant statistics
     */
    public function getEstatiscas(Tenant $tenant)
    {
        return [
            'total_associados' => $tenant->run(function () {
            return \App\Models\Associado::count();
        }),
            'receita_mes' => $tenant->run(function () {
            // implement logic
            return 0;
        }),
        ];
    }
}
