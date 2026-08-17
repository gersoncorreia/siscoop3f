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
            $table->string('forma_pagamento')->nullable()->after('status');
            $table->string('comprovante_path')->nullable()->after('forma_pagamento');
            $table->timestamp('data_pagamento')->nullable()->after('comprovante_path');
            $table->text('observacoes_pagamento')->nullable()->after('data_pagamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lancamento_financeiros', function (Blueprint $table) {
            $table->dropColumn(['forma_pagamento', 'comprovante_path', 'data_pagamento', 'observacoes_pagamento']);
        });
    }
};
