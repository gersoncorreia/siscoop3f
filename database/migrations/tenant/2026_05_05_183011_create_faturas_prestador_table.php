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
        Schema::create('faturas_prestador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestador_id')->constrained('prestadors')->onDelete('cascade');
            $table->string('mes_referencia'); // e.g., '2026-04'
            $table->decimal('valor_total', 10, 2);
            $table->string('arquivo_guia')->nullable();
            $table->string('status')->default('enviada'); // enviada, em_auditoria, paga
            $table->timestamp('data_pagamento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faturas_prestador');
    }
};
