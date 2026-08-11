<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanhas_saude', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao');
            $table->string('tipo'); // vacinacao, checkup, prevencao, promocao, educativa, outro
            $table->string('publico_alvo')->default('todos'); // todos, individual, familiar, empresarial, institucional, idosos, criancas, gestantes
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('imagem_path')->nullable();
            $table->unsignedInteger('meta_participantes')->nullable();
            $table->unsignedInteger('total_participantes')->default(0);
            $table->boolean('notificar_membros')->default(false);
            $table->json('meta_dados')->nullable(); // Dados extras flexíveis
            $table->timestamps();

            $table->index('ativo');
            $table->index('tipo');
            $table->index(['data_inicio', 'data_fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campanhas_saude');
    }
};
