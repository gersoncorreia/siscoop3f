<?php
foreach (\App\Models\Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Prestador', 'guard_name' => 'web']);
    echo "Role Prestador criada no tenant: {$tenant->id}\n";
}
