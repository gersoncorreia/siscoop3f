<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona campos de controle financeiro ao formulário de adesão:
     * - forma_pagamento_adesao: método de pagamento escolhido (PIX, Boleto, etc.)
     * - autoriza_desconto_folha: autorização obrigatória para desconto em folha
     */
    public function up(): void
    {
        Schema::table('associados', function (Blueprint $table) {
            $table->string('forma_pagamento_adesao')->nullable()->after('data_adesao');
            $table->boolean('autoriza_desconto_folha')->default(false)->after('forma_pagamento_adesao');
        });
    }

    public function down(): void
    {
        Schema::table('associados', function (Blueprint $table) {
            $table->dropColumn(['forma_pagamento_adesao', 'autoriza_desconto_folha']);
        });
    }
};
