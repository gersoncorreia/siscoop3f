<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitacaoInternacao;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InternacaoAdminController extends Controller
{
    public function index()
    {
        $internacoes = SolicitacaoInternacao::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Internacoes/Index', [
            'internacoes' => $internacoes,
        ]);
    }

    public function aprovar(SolicitacaoInternacao $internacao)
    {
        $internacao->update(['status' => 'aprovada', 'motivo_rejeicao' => null]);
        return back()->with('success', 'Internação aprovada com sucesso.');
    }

    public function rejeitar(Request $request, SolicitacaoInternacao $internacao)
    {
        $request->validate(['motivo_rejeicao' => 'required|string|max:1000']);
        $internacao->update([
            'status'          => 'rejeitada',
            'motivo_rejeicao' => $request->motivo_rejeicao,
        ]);
        return back()->with('success', 'Internação rejeitada.');
    }
}
