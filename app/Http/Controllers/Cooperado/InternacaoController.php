<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Models\SolicitacaoInternacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InternacaoController extends Controller
{
    public function index()
    {
        $internacoes = SolicitacaoInternacao::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Cooperado/Internacoes', [
            'internacoes' => $internacoes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hospital_clinica'       => 'required|string|max:255',
            'procedimento_principal' => 'required|string|max:255',
            'data_prevista'          => 'required|date|after_or_equal:today',
            'crm_medico'             => 'required|string|max:20',
            'nome_medico'            => 'required|string|max:255',
            'justificativa_clinica'  => 'nullable|string|max:2000',
            'anexo_guia'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('anexo_guia')) {
            $path = $request->file('anexo_guia')->store('internacoes/guias', 'public');
        }

        SolicitacaoInternacao::create([
            'user_id'                => auth()->id(),
            'hospital_clinica'       => $validated['hospital_clinica'],
            'procedimento_principal' => $validated['procedimento_principal'],
            'data_prevista'          => $validated['data_prevista'],
            'crm_medico'             => $validated['crm_medico'],
            'nome_medico'            => $validated['nome_medico'],
            'justificativa_clinica'  => $validated['justificativa_clinica'] ?? null,
            'anexo_guia_path'        => $path,
            'status'                 => 'analise',
        ]);

        return redirect()->route('cooperado.internacoes.index')
            ->with('success', 'Solicitação de internação enviada! Aguarde análise.');
    }
}
