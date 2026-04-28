<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
$app = app();
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
tenancy()->initialize('stfsaude');
$assoc = App\Models\Associado::find(2);
if ($assoc && $assoc->user_id) {
    App\Models\User::destroy($assoc->user_id);
    $assoc->update(['status' => 'analise_documental', 'user_id' => null]);
    echo 'Revertido!';
} else {
    echo 'Não precisou reverter!';
}
