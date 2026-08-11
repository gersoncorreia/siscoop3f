<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use Inertia\Inertia;

class AgendamentoAdminController extends Controller
{
    public function index()
    {
        $agendamentos = Agendamento::with('user', 'prestador')
            ->orderBy('data_hora', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Agendamentos/Index', [
            'agendamentos' => $agendamentos,
        ]);
    }

    public function confirmar(Agendamento $agendamento)
    {
        $agendamento->update(['status' => 'confirmado']);
        return back()->with('success', 'Agendamento confirmado com sucesso.');
    }

    public function cancelar(Agendamento $agendamento)
    {
        $agendamento->update(['status' => 'cancelado']);
        return back()->with('success', 'Agendamento cancelado.');
    }

    public function realizar(Agendamento $agendamento)
    {
        $agendamento->update(['status' => 'realizado']);
        return back()->with('success', 'Agendamento marcado como realizado.');
    }
}
