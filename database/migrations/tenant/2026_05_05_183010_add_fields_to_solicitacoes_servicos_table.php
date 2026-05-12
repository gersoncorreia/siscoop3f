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
            $table->foreignId('servico_id')->nullable()->constrained('prestador_servicos')->onDelete('set null');
            $table->unsignedBigInteger('fatura_id')->nullable(); // Can't add constrained yet if fatura table is created after, or we just don't strictly constrain it here
            $table->decimal('valor_cobrado', 10, 2)->nullable();
            $table->timestamp('data_realizacao')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitacoes_servicos', function (Blueprint $table) {
            $table->dropForeign(['servico_id']);
            $table->dropColumn(['servico_id', 'fatura_id', 'valor_cobrado', 'data_realizacao']);
        });
    }
};
