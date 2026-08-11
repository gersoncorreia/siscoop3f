<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Models\ProntuarioHistorico;
use Inertia\Inertia;

class ProntuarioController extends Controller
{
    public function index()
    {
        $historico = ProntuarioHistorico::where('user_id', auth()->id())
            ->orderBy('data_atendimento', 'desc')
            ->get();

        return Inertia::render('Cooperado/Prontuario', [
            'historico' => $historico,
        ]);
    }
}
