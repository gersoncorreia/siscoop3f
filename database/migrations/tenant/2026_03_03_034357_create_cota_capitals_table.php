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
        Schema::create('cota_capitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('associado_id')->constrained()->onDelete('cascade');
            $table->decimal('valor_quota', 10, 2);
            $table->integer('qtd_quotas');
            $table->decimal('saldo', 10, 2)->default(0);
            $table->string('tipo_movimento')->nullable(); // Ex: integralizacao, devolucao
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cota_capitals');
    }
};
