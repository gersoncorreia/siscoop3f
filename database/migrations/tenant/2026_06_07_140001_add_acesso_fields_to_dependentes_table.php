<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expande a tabela de dependentes com:
     * - tipo_dependencia: 'legal' ou 'nao_legal' (afeta valor da taxa)
     * - autoriza_acesso: se o titular autorizou criação de usuário para este dependente
     * - email: necessário quando autoriza_acesso = true, para criar o login
     * - user_id: FK para o usuário criado (se houver acesso autorizado)
     */
    public function up(): void
    {
        Schema::table('dependentes', function (Blueprint $table) {
            $table->enum('tipo_dependencia', ['legal', 'nao_legal'])->default('legal')->after('parentesco');
            $table->boolean('autoriza_acesso')->default(false)->after('tipo_dependencia');
            $table->text('email')->nullable()->after('autoriza_acesso');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('dependentes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['tipo_dependencia', 'autoriza_acesso', 'email', 'user_id']);
        });
    }
};
