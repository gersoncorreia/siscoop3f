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
        Schema::table('cota_capitals', function (Blueprint $table) {
            $table->decimal('meta_total', 10, 2)->default(600.00)->after('saldo');
            $table->decimal('valor_mensal', 10, 2)->default(10.00)->after('meta_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cota_capitals', function (Blueprint $table) {
            $table->dropColumn(['meta_total', 'valor_mensal']);
        });
    }
};
