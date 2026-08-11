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
        Schema::create('lgpd_settings', function (Blueprint $table) {
            $table->id();
            $table->string('titulo')->default('Termos de Uso e Privacidade (LGPD)');
            $table->text('introducao')->nullable();
            $table->json('secoes')->nullable();
            $table->string('botao_texto')->default('Eu Li e Concordo com os Termos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lgpd_settings');
    }
};
