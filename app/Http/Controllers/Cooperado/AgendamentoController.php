<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Prestador;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgendamentoController extends Controller
{
    protected function getPaymentConfig(): array
    {
        $tenant = tenant();
        $defaultConfig = [
            'pix_manual' => [
                'enabled' => true,
                'tipo_chave' => 'cnpj',
                'chave_pix' => '',
                'favorecido' => $tenant->nome ?? 'Santa Casa da Amazônia'
            ]
        ];

        $rawConfig = $tenant->payment_config;
        if (is_string($rawConfig)) {
            $rawConfig = json_decode($rawConfig, true) ?? [];
        }
        if (!is_array($rawConfig)) {
            $rawConfig = [];
        }
        $paymentConfig = array_replace_recursive($defaultConfig, $rawConfig);

        if (!empty($paymentConfig['pix_manual']['qrcode_image_path'])) {
            $paymentConfig['pix_manual']['qrcode_image_url'] = tenant_asset($paymentConfig['pix_manual']['qrcode_image_path']);
        } elseif (!empty($paymentConfig['pix_manual']['qrcode_image_url']) && !str_starts_with($paymentConfig['pix_manual']['qrcode_image_url'], 'http')) {
            $path = str_replace('/storage/', '', $paymentConfig['pix_manual']['qrcode_image_url']);
            $paymentConfig['pix_manual']['qrcode_image_url'] = tenant_asset($path);
        }

        return $paymentConfig;
    }

    public function index()
    {
        $paymentConfig = $this->getPaymentConfig();

        $agendamentos = Agendamento::where('user_id', auth()->id())
            ->with(['prestador', 'tabelaServico'])
            ->orderBy('data_hora', 'desc')
            ->get()
            ->map(function ($ag) {
                $valor = (float) ($ag->valor_cobrado ?? $ag->tabelaServico?->valor_base ?? 0);
                
                $mapPagamento = [
                    'pix_manual' => '📲 Pagamento via PIX (Online)',
                    'cartao_presencial' => '💳 Cartão no Guichê da Santa Casa',
                    'presencial' => '💵 Dinheiro na Recepção',
                ];

                $statusPagamentoLabel = $mapPagamento[$ag->forma_pagamento] ?? 'Pagamento Presencial na Recepção';
                if ($ag->status === 'confirmado' || $ag->status === 'realizado') {
                    $statusPagamentoLabel = '✅ Pago e Confirmado';
                } elseif ($ag->status === 'cancelado') {
                    $statusPagamentoLabel = 'Solicitação Cancelada';
                } elseif ($valor == 0) {
                    $statusPagamentoLabel = 'Incluso no Plano / Isento';
                }

                return [
                    'id' => $ag->id,
                    'tipo' => $ag->tipo,
                    'especialidade' => $ag->especialidade,
                    'modalidade' => $ag->modalidade,
                    'data_hora' => $ag->data_hora ? $ag->data_hora->format('Y-m-d H:i:s') : null,
                    'status' => $ag->status,
                    'forma_pagamento' => $ag->forma_pagamento ?? 'presencial',
                    'comprovante_pix_url' => $ag->comprovante_pix_url,
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
            'paymentConfig' => $paymentConfig,
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
        $paymentConfig = $this->getPaymentConfig();

        return Inertia::render('Cooperado/Agendamentos/Criar', [
            'prestadores' => $prestadores,
            'tabelaServicos' => $tabelaServicos,
            'selectedServico' => $selectedServico,
            'paymentConfig' => $paymentConfig,
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
            'forma_pagamento'   => 'required|in:presencial,pix_manual,cartao_presencial',
            'comprovante_pix'   => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:4096',
            'observacoes'       => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'pendente';

        if ($request->hasFile('comprovante_pix')) {
            $path = $request->file('comprovante_pix')->store('agendamentos/comprovantes', 'public');
            $validated['comprovante_pix_path'] = $path;
        }

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

            $obs = "Agendamento Santa Casa: {$nomeServico} ({$validated['especialidade']})";
            if (!empty($validated['comprovante_pix_path'])) {
                $obs .= ' - Comprovante PIX Anexado pelo Cliente';
            }

            \App\Models\LancamentoFinanceiro::create([
                'associado_id' => $associado->id,
                'tipo' => 'receita',
                'categoria' => $categoriaFinanceira,
                'valor' => $validated['valor_cobrado'],
                'data_vencimento' => $validated['data_hora'],
                'status' => 'pendente',
                'forma_pagamento' => $validated['forma_pagamento'],
                'observacoes_pagamento' => $obs,
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

    public function anexarComprovante(Request $request, Agendamento $agendamento)
    {
        if ($agendamento->user_id !== auth()->id()) {
            abort(403);
        }

        if ($agendamento->status !== 'pendente') {
            return back()->with('error', 'Não é possível anexar comprovante a um agendamento já confirmado ou finalizado.');
        }

        $request->validate([
            'comprovante_pix' => 'required|file|mimes:jpeg,jpg,png,pdf|max:4096',
        ]);

        $path = $request->file('comprovante_pix')->store('agendamentos/comprovantes', 'public');
        $agendamento->update([
            'comprovante_pix_path' => $path,
            'forma_pagamento' => 'pix_manual',
        ]);

        return back()->with('success', 'Comprovante PIX anexado ao agendamento com sucesso!');
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
