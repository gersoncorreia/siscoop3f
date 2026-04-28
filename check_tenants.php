<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = \App\Models\Tenant::with('domains')->get();
echo "Total Tenants: " . $tenants->count() . "\n";
foreach ($tenants as $t) {
    echo "Tenant {$t->id} | DB: " . $t->tenancy_db_name . "\n";
    foreach ($t->domains as $d) {
        echo "  Domain: {$d->domain}\n";
    }
    tenancy()->initialize($t);
    $users = \App\Models\User::count();
    $associados = \App\Models\Associado::count();
    echo "  Users: $users, Associados: $associados\n";
    tenancy()->end();
}
