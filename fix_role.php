<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
$app = app();
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('stfsaude');
$u = App\Models\User::find(2);
$u->assignRole('Presidente');
echo 'Atribuido!';
