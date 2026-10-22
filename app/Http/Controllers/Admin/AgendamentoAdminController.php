<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Associado;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgendamentoAdminController extends Controller
{
    public function index()
    {
        $stats = [
            'totalPendentes'  => Agendamento::where('status', 'pendente')->count(),
            'totalConfirmados' => Agendamento::where('status', 'confirmado')->count(),
            'totalPixPendente' => Agendamento::where('forma_pagamento', 'pix_manual')->whereNotNull('comprovante_pix_path')->where('status', 'pendente')->count(),
            'totalMes'         => Agendamento::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        $agendamentos = Agendamento::with(['user', 'prestador', 'tabelaServico'])
            ->orderBy('data_hora', 'desc')
            ->paginate(20)
            ->through(function ($ag) {
                $associado = Associado::where('user_id', $ag->user_id)->with(['telefones', 'enderecos'])->first();
                $valor = (float) ($ag->valor_cobrado ?? $ag->tabelaServico?->valor_base ?? 0);

                $mapPagamento = [
                    'pix_manual' => '📲 PIX Manual (Online)',
                    'cartao_presencial' => '💳 Cartão no Guichê',
                    'presencial' => '💵 Dinheiro na Recepção',
                ];

                return [
                    'id' => $ag->id,
                    'tipo' => $ag->tipo,
                    'especialidade' => $ag->especialidade,
                    'modalidade' => $ag->modalidade,
                    'data_hora' => $ag->data_hora ? $ag->data_hora->format('Y-m-d H:i:s') : null,
                    'status' => $ag->status,
                    'forma_pagamento' => $ag->forma_pagamento ?? 'presencial',
                    'forma_pagamento_label' => $mapPagamento[$ag->forma_pagamento] ?? 'Dinheiro na Recepção',
                    'comprovante_pix_url' => $ag->comprovante_pix_url,
                    'comprovante_pix_path' => $ag->comprovante_pix_path,
                    'valor_cobrado' => $valor,
                    'observacoes' => $ag->observacoes,
                    'created_at' => $ag->created_at ? $ag->created_at->format('d/m/Y H:i') : null,
                    'cliente' => [
                        'id' => $ag->user?->id,
                        'name' => $ag->user?->name ?? 'Cliente Desconhecido',
                        'email' => $ag->user?->email,
                        'cpf' => $associado?->cpf,
                        'matricula' => $associado?->matricula,
                        'telefone' => $associado?->telefones->first()?->numero ?? '—',
                    ],
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

        return Inertia::render('Admin/Agendamentos/Index', [
            'agendamentos' => $agendamentos,
            'stats' => $stats,
        ]);
    }

    public function aprovarEAgendar(Request $request, Agendamento $agendamento)
    {
        if ($agendamento->status === 'realizado') {
            return back()->with('error', 'Agendamentos já realizados não podem ter seu status alterado.');
        }

        $validated = $request->validate([
            'data_hora'   => 'nullable|date',
            'observacoes' => 'nullable|string|max:1000',
        ]);

        $dataHora = !empty($validated['data_hora']) ? $validated['data_hora'] : $agendamento->data_hora;

        $agendamento->update([
            'status' => 'confirmado',
            'data_hora' => $dataHora,
            'observacoes' => $validated['observacoes'] ?? $agendamento->observacoes,
        ]);

        // Notificar Cliente
        if ($agendamento->user) {
            $dataFormatted = \Carbon\Carbon::parse($dataHora)->format('d/m/Y H:i');
            $agendamento->user->notify(new \App\Notifications\AppDatabaseNotification(
                'Agendamento Confirmado',
                "Seu agendamento de {$agendamento->especialidade} foi confirmado para {$dataFormatted} pela Santa Casa.",
                route('cooperado.agendamentos.index'),
                'CheckCircleIcon'
            ));
        }

        return back()->with('success', 'Agendamento aprovado e confirmado com sucesso.');
    }

    public function recusar(Request $request, Agendamento $agendamento)
    {
        if ($agendamento->status === 'realizado') {
            return back()->with('error', 'Agendamentos já realizados não podem ser recusados.');
        }

        $validated = $request->validate([
            'motivo' => 'required|string|max:1000',
        ]);

        $agendamento->update([
            'status' => 'cancelado',
            'observacoes' => "Recusado pela Santa Casa: {$validated['motivo']}",
        ]);

        // Notificar Cliente
        if ($agendamento->user) {
            $agendamento->user->notify(new \App\Notifications\AppDatabaseNotification(
                'Agendamento Cancelado',
                "Seu agendamento de {$agendamento->especialidade} foi cancelado pela Santa Casa. Motivo: {$validated['motivo']}",
                route('cooperado.agendamentos.index'),
                'XCircleIcon'
            ));
        }

        return back()->with('success', 'Agendamento cancelado e cliente notificado.');
    }

    public function realizar(Agendamento $agendamento)
    {
        if ($agendamento->status === 'realizado') {
            return back()->with('info', 'Este agendamento já foi marcado como realizado.');
        }

        $agendamento->update(['status' => 'realizado']);

        // Baixar lançamento financeiro se houver pendente vinculado ao agendamento
        $associado = Associado::where('user_id', $agendamento->user_id)->first();
        if ($associado) {
            \App\Models\LancamentoFinanceiro::where('associado_id', $associado->id)
                ->where('status', 'pendente')
                ->where('valor', $agendamento->valor_cobrado)
                ->where('created_at', '>=', $agendamento->created_at->subMinutes(5))
                ->update(['status' => 'pago', 'data_pagamento' => now()]);
        }

        return back()->with('success', 'Agendamento marcado como realizado com sucesso.');
    }

    public function confirmar(Request $request, Agendamento $agendamento)
    {
        return $this->aprovarEAgendar($request, $agendamento);
    }

    public function cancelar(Request $request, Agendamento $agendamento)
    {
        if (!$request->filled('motivo')) {
            $request->merge(['motivo' => 'Cancelado pela administração']);
        }
        return $this->recusar($request, $agendamento);
    }
}
