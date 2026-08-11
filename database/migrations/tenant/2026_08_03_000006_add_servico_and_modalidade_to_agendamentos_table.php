<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('tabela_servico_id')->nullable()->after('prestador_id');
            $table->decimal('valor_cobrado', 10, 2)->nullable()->after('tabela_servico_id');
            $table->string('modalidade')->default('presencial')->after('valor_cobrado'); // presencial, telemedicina

            $table->foreign('tabela_servico_id')->references('id')->on('tabela_servicos')->nullOnDelete();
            $table->index('modalidade');
        });
    }

    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropForeign(['tabela_servico_id']);
            $table->dropIndex(['modalidade']);
            $table->dropColumn(['tabela_servico_id', 'valor_cobrado', 'modalidade']);
        });
    }
};
