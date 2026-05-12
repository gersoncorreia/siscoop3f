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
        Schema::create('solicitacoes_servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('associado_id')->constrained()->onDelete('cascade');
            $table->foreignId('prestador_id')->constrained('prestadors')->onDelete('cascade'); // Assuming the table is prestadors
            $table->string('status')->default('pendente'); // pendente, aprovado, recusado
            $table->text('observacoes')->nullable();
            $table->text('motivo_recusa')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_servicos');
    }
};
