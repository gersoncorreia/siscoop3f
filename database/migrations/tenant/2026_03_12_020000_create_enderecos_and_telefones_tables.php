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
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('associado_id')->constrained('associados')->onDelete('cascade');
            $table->string('tipo')->default('residencial'); // residencial, comercial, etc
            $table->text('logradouro'); // encrypted
            $table->string('numero')->nullable();
            $table->text('complemento')->nullable(); // encrypted
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();
        });

        Schema::create('telefones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('associado_id')->constrained('associados')->onDelete('cascade');
            $table->string('tipo')->default('celular'); // celular, fixo, whatsapp
            $table->text('numero'); // encrypted
            $table->boolean('principal')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telefones');
        Schema::dropIfExists('enderecos');
    }
};
