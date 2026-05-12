<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestador extends Model
{
    protected $fillable = ['user_id', 'tipo', 'nome', 'especialidades', 'endereco'];

    protected $casts = [
        'especialidades' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function servicos()
    {
        return $this->hasMany(PrestadorServico::class);
    }

    public function faturas()
    {
        return $this->hasMany(FaturaPrestador::class);
    }
}
