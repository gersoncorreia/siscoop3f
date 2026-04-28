<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Dependente extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'associado_id',
        'nome',
        'cpf',
        'data_nascimento',
        'parentesco',
        'status',
    ];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
}
