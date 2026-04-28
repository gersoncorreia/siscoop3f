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

    protected $fillable = ['associado_id', 'tipo', 'valor', 'data_vencimento', 'status'];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }
}
