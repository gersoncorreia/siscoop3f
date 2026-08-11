<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('associados', function (Blueprint $table) {
            $table->string('carteirinha_numero', 20)->unique()->nullable()->after('matricula');
            $table->string('carteirinha_tipo')->nullable()->after('carteirinha_numero'); // individual, familiar
            $table->unsignedBigInteger('plano_adesao_id')->nullable()->after('carteirinha_tipo');
            $table->date('carteirinha_validade')->nullable()->after('plano_adesao_id');
            $table->string('carteirinha_status')->default('pendente')->after('carteirinha_validade'); // ativa, suspensa, cancelada, pendente
            $table->dateTime('carteirinha_emitida_em')->nullable()->after('carteirinha_status');

            $table->foreign('plano_adesao_id')->references('id')->on('planos_adesao')->nullOnDelete();
            $table->index('carteirinha_status');
        });
    }

    public function down(): void
    {
        Schema::table('associados', function (Blueprint $table) {
            $table->dropForeign(['plano_adesao_id']);
            $table->dropIndex(['carteirinha_status']);
            $table->dropColumn([
                'carteirinha_numero',
                'carteirinha_tipo',
                'plano_adesao_id',
                'carteirinha_validade',
                'carteirinha_status',
                'carteirinha_emitida_em',
            ]);
        });
    }
};
