<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
tenancy()->initialize(\App\Models\Tenant::find('stfsaude'));
echo "URL for lgpd.show: " . route('lgpd.show') . "\n";
