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
        Schema::table('associados', function (Blueprint $table) {
            $table->string('matricula')->nullable()->after('cpf')->comment('Matrícula do associado gerada ou preenchida manualmente.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('associados', function (Blueprint $table) {
            $table->dropColumn('matricula');
        });
    }
};
