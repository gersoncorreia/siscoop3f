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
                if (Schema::hasColumn('dependentes', 'cpf')) {
                    $table->text('cpf')->nullable()->change();
                }
                if (Schema::hasColumn('dependentes', 'email')) {
                    $table->text('email')->nullable()->change();
                }
                if (Schema::hasColumn('dependentes', 'rg')) {
                    $table->text('rg')->nullable()->change();
                }
                if (Schema::hasColumn('dependentes', 'cartao_sus')) {
                    $table->text('cartao_sus')->nullable()->change();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('dependentes')) {
            Schema::table('dependentes', function (Blueprint $table) {
                if (Schema::hasColumn('dependentes', 'cpf')) {
                    $table->string('cpf', 191)->nullable()->change();
                }
                if (Schema::hasColumn('dependentes', 'email')) {
                    $table->string('email', 191)->nullable()->change();
                }
                if (Schema::hasColumn('dependentes', 'rg')) {
                    $table->string('rg', 191)->nullable()->change();
                }
                if (Schema::hasColumn('dependentes', 'cartao_sus')) {
                    $table->string('cartao_sus', 191)->nullable()->change();
                }
            });
        }
    }
};
