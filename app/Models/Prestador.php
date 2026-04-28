<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestador extends Model
{
    protected $fillable = ['tipo', 'nome', 'especialidades', 'endereco'];

    protected $casts = [
        'especialidades' => 'array',
    ];
}
