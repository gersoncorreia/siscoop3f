<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Dependente;
use App\Models\Agendamento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class DependentePortalController extends Controller
{
    /**
     * Exibe o Portal do Dependente autenticado via Link Mágico
     */
    public function index()
    {
        $dependenteId = session('dependente_autenticado_id');

        if (!$dependenteId) {
            return Redirect::route('login')->with('error', 'Sessão expirada. Acesse através do seu Link Mágico.');
        }

        $dependente = Dependente::with(['associado'])->find($dependenteId);

        if (!$dependente) {
            session()->forget(['dependente_autenticado_id', 'dependente_autenticado_token']);
            return Redirect::route('login')->with('error', 'Dependente não encontrado.');
        }

        $associado = $dependente->associado;

        // Buscar agendamentos vinculados ao titular ou por nome do dependente
        $agendamentos = [];
        if ($associado && $associado->user_id) {
            $agendamentos = Agendamento::where('user_id', $associado->user_id)
                ->with(['tabelaServico', 'prestador'])
                ->orderBy('data_hora', 'desc')
                ->get()
                ->map(function ($ag) {
                    return [
                        'id' => $ag->id,
                        'especialidade' => $ag->especialidade,
                        'servico_nome' => $ag->tabelaServico?->nome,
                        'prestador_nome' => $ag->prestador?->nome ?? '—',
                        'data_hora' => $ag->data_hora ? $ag->data_hora->format('d/m/Y H:i') : '—',
                        'valor' => (float) ($ag->valor_cobrado ?? $ag->tabelaServico?->valor_base ?? 0),
                        'status' => $ag->status,
                        'status_pagamento_label' => $ag->status_pagamento_label ?? $ag->status,
                    ];
                });
        }

        $tenant = tenant();

        return Inertia::render('Dependente/Portal', [
            'dependente' => [
                'id' => $dependente->id,
                'nome' => $dependente->nome,
                'cpf' => $dependente->cpf,
                'parentesco' => $dependente->parentesco,
                'tipo_dependencia' => $dependente->tipo_dependencia,
                'titular_nome' => $associado?->nome ?? 'Titular',
                'carteirinha_numero' => $associado?->carteirinha_numero ? $associado->carteirinha_numero . '-D' . $dependente->id : 'Em emissão',
                'carteirinha_validade' => $associado?->carteirinha_validade ? $associado->carteirinha_validade->format('d/m/Y') : '1 Ano',
                'plano_nome' => 'Plano de Saúde Santa Casa',
            ],
            'agendamentos' => $agendamentos,
            'tenant' => [
                'nome' => $tenant->nome ?? 'Fundação Santa Casa da Amazônia',
                'tipo' => $tenant->tipo ?? 'fundacao',
            ],
        ]);
    }
}
