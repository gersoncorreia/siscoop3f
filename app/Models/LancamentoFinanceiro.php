<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LancamentoFinanceiro extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'associado_id',
        'tipo',
        'categoria',
        'valor',
        'data_vencimento',
        'status',
        'forma_pagamento',
        'comprovante_path',
        'data_pagamento',
        'observacoes_pagamento',
        'gateway',
        'gateway_id',
    ];

    protected $casts = [
        'data_pagamento' => 'datetime',
        'data_vencimento' => 'date',
    ];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }
}
