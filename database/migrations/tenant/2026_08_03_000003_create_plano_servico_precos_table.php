<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plano_servico_precos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plano_adesao_id');
            $table->unsignedBigInteger('tabela_servico_id');
            $table->decimal('valor_personalizado', 10, 2)->nullable(); // Se NULL, usa valor_base do serviço
            $table->decimal('desconto_percentual', 5, 2)->nullable(); // Desconto % sobre valor_base
            $table->boolean('incluso')->default(false); // Se o serviço está incluso no plano (sem custo adicional)
            $table->timestamps();

            $table->foreign('plano_adesao_id')->references('id')->on('planos_adesao')->onDelete('cascade');
            $table->foreign('tabela_servico_id')->references('id')->on('tabela_servicos')->onDelete('cascade');
            $table->unique(['plano_adesao_id', 'tabela_servico_id'], 'plano_servico_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plano_servico_precos');
    }
};
