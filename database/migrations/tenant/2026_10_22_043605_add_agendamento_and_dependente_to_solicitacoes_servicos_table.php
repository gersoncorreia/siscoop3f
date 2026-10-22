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
        Schema::table('solicitacoes_servicos', function (Blueprint $table) {
            $table->foreignId('dependente_id')->nullable()->after('associado_id')->constrained('dependentes')->nullOnDelete();
            $table->foreignId('agendamento_id')->nullable()->after('fatura_id')->constrained('agendamentos')->nullOnDelete();
            $table->dateTime('data_agendada')->nullable()->after('data_realizacao');
            $table->string('codigo_guia')->nullable()->unique()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitacoes_servicos', function (Blueprint $table) {
            $table->dropForeign(['dependente_id']);
            $table->dropForeign(['agendamento_id']);
            $table->dropColumn(['dependente_id', 'agendamento_id', 'data_agendada', 'codigo_guia']);
        });
    }
};
