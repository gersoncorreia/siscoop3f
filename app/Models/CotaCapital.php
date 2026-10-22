<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CotaCapital extends Model
{
    protected $fillable = ['associado_id', 'meta_total', 'valor_mensal', 'valor_quota', 'qtd_quotas', 'saldo', 'tipo_movimento'];

    public function associado()
    {
        return $this->belongsTo(Associado::class);
    }
}
