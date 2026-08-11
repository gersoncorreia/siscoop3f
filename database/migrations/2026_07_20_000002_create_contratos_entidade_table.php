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
        Schema::create('contratos_entidade', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_contratante_id');
            $table->string('tenant_prestador_id');
            $table->string('status')->default('pendente'); // pendente, ativo, suspenso, encerrado
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();

            $table->foreign('tenant_contratante_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('tenant_prestador_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos_entidade');
    }
};
