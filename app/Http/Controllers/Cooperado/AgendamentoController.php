<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Prestador;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgendamentoController extends Controller
{
    public function index()
    {
        $agendamentos = Agendamento::where('user_id', auth()->id())
            ->with(['prestador', 'tabelaServico'])
            ->orderBy('data_hora', 'desc')
            ->get()
            ->map(function ($ag) {
                $valor = (float) ($ag->valor_cobrado ?? $ag->tabelaServico?->valor_base ?? 0);
                $statusPagamentoLabel = 'Pagamento Presencial na Recepção';
                if ($valor == 0) {
                    $statusPagamentoLabel = 'Incluso no Plano / Isento';
                }

                return [
                    'id' => $ag->id,
                    'tipo' => $ag->tipo,
                    'especialidade' => $ag->especialidade,
                    'modalidade' => $ag->modalidade,
                    'data_hora' => $ag->data_hora ? $ag->data_hora->format('Y-m-d H:i:s') : null,
                    'status' => $ag->status,
                    'observacoes' => $ag->observacoes,
                    'valor_cobrado' => $valor,
                    'status_pagamento_label' => $statusPagamentoLabel,
                    'prestador' => $ag->prestador ? [
                        'id' => $ag->prestador->id,
                        'nome' => $ag->prestador->nome,
                    ] : null,
                    'tabela_servico' => $ag->tabelaServico ? [
                        'id' => $ag->tabelaServico->id,
                        'codigo' => $ag->tabelaServico->codigo,
                        'nome' => $ag->tabelaServico->nome,
                        'categoria' => $ag->tabelaServico->categoria,
                        'valor_base' => (float) $ag->tabelaServico->valor_base,
                    ] : null,
                ];
            });

        return Inertia::render('Cooperado/Agendamentos/Index', [
            'agendamentos' => $agendamentos,
        ]);
    }

    public function create(Request $request)
    {
        $prestadores = Prestador::orderBy('nome')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nome' => $p->nome,
                'especialidade' => is_array($p->especialidades) ? implode(', ', $p->especialidades) : ($p->especialidades ?? 'Geral'),
                'endereco' => $p->endereco,
            ];
        });

        $selectedServico = null;
        if ($request->filled('servico_id')) {
            $selectedServico = \App\Models\TabelaServico::find($request->query('servico_id'));
        }

        $tabelaServicos = \App\Models\TabelaServico::ativos()->ordenado()->get(['id', 'codigo', 'nome', 'categoria', 'valor_base']);

        return Inertia::render('Cooperado/Agendamentos/Criar', [
            'prestadores' => $prestadores,
            'tabelaServicos' => $tabelaServicos,
            'selectedServico' => $selectedServico,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo'              => 'required|in:consulta,exame,pronto_atendimento',
            'especialidade'     => 'required|string|max:255',
            'prestador_id'      => 'nullable|exists:prestadors,id',
            'tabela_servico_id' => 'nullable|exists:tabela_servicos,id',
            'data_hora'         => 'required|date|after:now',
            'observacoes'       => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'pendente';

        $servicoObj = null;
        if (!empty($validated['tabela_servico_id'])) {
            $servicoObj = \App\Models\TabelaServico::find($validated['tabela_servico_id']);
            if ($servicoObj) {
                $validated['valor_cobrado'] = $servicoObj->valor_base;
            }
        }

        $agendamento = Agendamento::create($validated);

        // Gerar lançamento financeiro do serviço se houver valor a ser cobrado
        $associado = \App\Models\Associado::where('user_id', auth()->id())->first();
        if ($associado && !empty($validated['valor_cobrado']) && $validated['valor_cobrado'] > 0) {
            $categoriaFinanceira = $validated['tipo'] === 'exame' ? 'exame' : 'consulta_medica';
            $nomeServico = $servicoObj ? $servicoObj->nome : $validated['especialidade'];

            \App\Models\LancamentoFinanceiro::create([
                'associado_id' => $associado->id,
                'tipo' => 'receita',
                'categoria' => $categoriaFinanceira,
                'valor' => $validated['valor_cobrado'],
                'data_vencimento' => $validated['data_hora'],
                'status' => 'pendente',
                'observacoes' => "Agendamento Santa Casa: {$nomeServico} ({$validated['especialidade']})",
                'gateway' => 'presencial',
            ]);
        }

        // Disparar Notificação para o Usuário
        $user = auth()->user();
        if ($user) {
            $tipoLabel = ucfirst($validated['tipo']);
            $user->notify(new \App\Notifications\AppDatabaseNotification(
                'Agendamento Solicitado',
                "Seu agendamento de {$tipoLabel} ({$validated['especialidade']}) foi registrado com sucesso na Santa Casa.",
                route('cooperado.agendamentos.index'),
                'CalendarDaysIcon'
            ));
        }

        return redirect()->route('cooperado.agendamentos.index')
            ->with('success', 'Agendamento solicitado com sucesso! Aguarde confirmação.');
    }

    public function destroy(Agendamento $agendamento)
    {
        if ($agendamento->user_id !== auth()->id()) {
            abort(403);
        }
        if (!in_array($agendamento->status, ['pendente'])) {
            return back()->with('error', 'Apenas agendamentos pendentes podem ser cancelados.');
        }
        $agendamento->update(['status' => 'cancelado']);

        // Disparar Notificação de Cancelamento
        $user = auth()->user();
        if ($user) {
            $user->notify(new \App\Notifications\AppDatabaseNotification(
                'Agendamento Cancelado',
                "Seu agendamento de {$agendamento->especialidade} foi cancelado.",
                route('cooperado.agendamentos.index'),
                'XCircleIcon'
            ));
        }

        return redirect()->route('cooperado.agendamentos.index')
            ->with('success', 'Agendamento cancelado.');
    }
}
