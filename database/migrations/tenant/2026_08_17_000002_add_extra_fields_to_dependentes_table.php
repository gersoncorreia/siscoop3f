<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dependentes')) {
            Schema::table('dependentes', function (Blueprint $table) {
                if (!Schema::hasColumn('dependentes', 'rg')) {
                    $table->string('rg', 30)->nullable()->after('cpf');
                }
                if (!Schema::hasColumn('dependentes', 'cartao_sus')) {
                    $table->string('cartao_sus', 30)->nullable()->after('rg');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('dependentes')) {
            Schema::table('dependentes', function (Blueprint $table) {
                if (Schema::hasColumn('dependentes', 'rg')) {
                    $table->dropColumn('rg');
                }
                if (Schema::hasColumn('dependentes', 'cartao_sus')) {
                    $table->dropColumn('cartao_sus');
                }
            });
        }
    }
};
