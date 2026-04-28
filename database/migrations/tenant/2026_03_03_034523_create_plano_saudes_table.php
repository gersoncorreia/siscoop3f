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
        Schema::create('plano_saudes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->json('coberturas')->nullable();
            $table->json('faixas_etarias')->nullable(); // Preços por idade
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_saudes');
    }
};
