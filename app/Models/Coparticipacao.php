<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coparticipacao extends Model
{
    protected $fillable = ['reembolso_id', 'valor_total', 'percentual', 'status'];

    public function reembolso()
    {
        return $this->belongsTo(Reembolso::class);
    }
}
