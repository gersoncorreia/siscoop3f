<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('associados', function (Blueprint $table) {
            $table->unsignedBigInteger('aprovado_por_user_id')->nullable()->after('status');
            $table->string('aprovado_por_nome')->nullable()->after('aprovado_por_user_id');
            $table->timestamp('aprovado_em')->nullable()->after('aprovado_por_nome');
            $table->string('forma_pagamento_aprovacao')->nullable()->after('aprovado_em');
            $table->text('observacoes_aprovacao')->nullable()->after('forma_pagamento_aprovacao');
            $table->integer('parcelas_aprovacao')->default(1)->after('observacoes_aprovacao');
            $table->string('comprovante_pix_path')->nullable()->after('parcelas_aprovacao');
            $table->json('detalhes_financeiro_aprovacao')->nullable()->after('comprovante_pix_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('associados', function (Blueprint $table) {
            $table->dropColumn([
                'aprovado_por_user_id',
                'aprovado_por_nome',
                'aprovado_em',
                'forma_pagamento_aprovacao',
                'observacoes_aprovacao',
                'parcelas_aprovacao',
                'comprovante_pix_path',
                'detalhes_financeiro_aprovacao',
            ]);
        });
    }
};
