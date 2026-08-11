<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabela_servicos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nome');
            $table->string('categoria'); // consulta, exame_laboratorial, exame_imagem, procedimento, cirurgia, internacao, fisioterapia, odontologia, pronto_atendimento, outros
            $table->text('descricao')->nullable();
            $table->decimal('valor_base', 10, 2);
            $table->boolean('ativo')->default(true);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->index('categoria');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabela_servicos');
    }
};
