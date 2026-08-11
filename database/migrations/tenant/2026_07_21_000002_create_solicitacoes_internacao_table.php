<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_internacao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('hospital_clinica');
            $table->string('procedimento_principal');
            $table->date('data_prevista');
            $table->string('crm_medico');
            $table->string('nome_medico');
            $table->text('justificativa_clinica')->nullable();
            $table->string('anexo_guia_path')->nullable();
            $table->string('status')->default('analise'); // analise, aprovada, rejeitada
            $table->text('motivo_rejeicao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_internacao');
    }
};
