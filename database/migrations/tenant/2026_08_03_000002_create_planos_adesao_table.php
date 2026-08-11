<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planos_adesao', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // "Cartão Santa Casa PF", "Cartão Família", "Plano Empresarial"
            $table->string('tipo'); // individual, familiar, empresarial, institucional
            $table->decimal('valor_adesao', 10, 2)->default(0); // Valor da adesão/cartão
            $table->decimal('valor_mensalidade', 10, 2)->default(0); // Se aplicável
            $table->text('descricao')->nullable();
            $table->unsignedInteger('max_dependentes')->default(0); // 0 = ilimitado para familiar
            $table->unsignedInteger('min_membros')->default(1); // Mínimo para empresarial/institucional
            $table->json('beneficios')->nullable(); // Lista de benefícios textuais
            $table->boolean('ativo')->default(true);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->index('tipo');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planos_adesao');
    }
};
