<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->string('forma_pagamento')->nullable()->after('status');
            $table->string('comprovante_pix_path')->nullable()->after('forma_pagamento');
        });
    }

    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropColumn(['forma_pagamento', 'comprovante_pix_path']);
        });
    }
};
