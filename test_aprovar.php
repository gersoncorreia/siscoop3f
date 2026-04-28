<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

$app = app();
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

tenancy()->initialize('stfsaude');

$associado = App\Models\Associado::where('status', 'analise_documental')->first();

if (!$associado) {
    echo "Nenhum associado pendente!\n";
    exit;
}

try {
    $service = app(App\Services\AdesaoService::class);
    $service->aprovarAdesao($associado);
    echo "Sucesso ao aprovar $associado->nome!\n";
} catch (\Throwable $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
