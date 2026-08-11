<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prontuario_historico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('medico_nome');
            $table->string('crm');
            $table->date('data_atendimento');
            $table->string('diagnostico_principal');
            $table->text('prescricao')->nullable();
            $table->text('observacoes_clinicas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prontuario_historico');
    }
};
