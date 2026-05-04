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
        Schema::table('lancamento_financeiros', function (Blueprint $table) {
            $table->string('categoria')->nullable()->after('tipo');
            $table->string('gateway')->default('interno')->after('status');
            $table->string('gateway_id')->nullable()->after('gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lancamento_financeiros', function (Blueprint $table) {
            $table->dropColumn(['categoria', 'gateway', 'gateway_id']);
        });
    }
};
