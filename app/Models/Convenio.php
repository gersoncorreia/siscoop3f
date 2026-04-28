<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Convenio extends Model
{
    protected $fillable = [
        'nome_empresa',
        'ramo',
        'desconto',
        'contato',
        'descricao',
        'ativo'
    ];
}
