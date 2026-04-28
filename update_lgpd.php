<?php
foreach(\App\Models\Tenant::all() as $t) {
    tenancy()->initialize($t);
    \App\Models\User::query()->update(['accepted_lgpd_at' => now()]);
}
echo "LGPD Updated\n";
