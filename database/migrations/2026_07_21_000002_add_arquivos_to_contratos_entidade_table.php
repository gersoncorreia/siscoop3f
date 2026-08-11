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
        Schema::connection('central')->table('contratos_entidade', function (Blueprint $table) {
            if (!Schema::connection('central')->hasColumn('contratos_entidade', 'arquivos')) {
                $table->json('arquivos')->nullable()->after('descricao');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('contratos_entidade', function (Blueprint $table) {
            if (Schema::connection('central')->hasColumn('contratos_entidade', 'arquivos')) {
                $table->dropColumn('arquivos');
            }
        });
    }
};
