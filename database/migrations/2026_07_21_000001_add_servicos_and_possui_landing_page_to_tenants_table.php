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
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'servicos')) {
                $table->json('servicos')->nullable()->after('tipo');
            }
            if (!Schema::hasColumn('tenants', 'possui_landing_page')) {
                $table->boolean('possui_landing_page')->default(true)->after('servicos');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'servicos')) {
                $table->dropColumn('servicos');
            }
            if (Schema::hasColumn('tenants', 'possui_landing_page')) {
                $table->dropColumn('possui_landing_page');
            }
        });
    }
};
