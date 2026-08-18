<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Associado;
use App\Http\Requests\Associado\StoreAssociadoRequest;
use App\Http\Requests\Associado\UpdateAssociadoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class AssociadoController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Associado::class);

        return Inertia::render('Associados/Index', [
            'associados' => Associado::latest()->paginate(10)->through(fn($a) => [
                'id' => $a->id,
                'nome' => $a->nome,
                'email' => $a->email,
                'matricula' => $a->matricula,
                'status' => $a->status,
                'cpf_masked' => $a->cpf ? substr($a->cpf, 0, 3) . '.***.***-' . substr($a->cpf, -2) : null,
                'data_adesao' => $a->data_adesao,
                'created_at' => $a->created_at->format('d/m/Y'),
            ])
        ]);
    }

    public function create()
    {
        $this->authorize('create', Associado::class);
        return Inertia::render('Associados/Form');
    }

    public function store(StoreAssociadoRequest $request)
    {
        $this->authorize('create', Associado::class);

        Associado::create($request->validated());

        return Redirect::route('associados.index')->with('success', 'Associado criado com sucesso!');
    }

    public function show(Associado $associado)
    {
        $this->authorize('view', $associado);

        $associado->load([
            'dependentes',
            'enderecos',
            'telefones',
            'documentos',
            'aprovador',
        ]);

        // Agendamentos / Atendimentos (Fundação)
        $agendamentos = [];
        if ($associado->user_id) {
            $agendamentos = \App\Models\Agendamento::where('user_id', $associado->user_id)
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
                        'forma_pagamento' => $ag->forma_pagamento,
                        'status' => $ag->status,
                        'status_pagamento_label' => $ag->status_pagamento_label ?? $ag->status,
                        'comprovante_pix_url' => $ag->comprovante_pix_path ? tenant_asset($ag->comprovante_pix_path) : null,
                    ];
                });
        }

        // Lançamentos Financeiros
        $lancamentos = \App\Models\LancamentoFinanceiro::where('associado_id', $associado->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'descricao' => $l->descricao ?? $l->categoria ?? 'Lançamento',
                    'categoria' => $l->categoria,
                    'tipo' => $l->tipo,
                    'valor' => (float) $l->valor,
                    'status' => $l->status,
                    'data_vencimento' => $l->data_vencimento ? \Carbon\Carbon::parse($l->data_vencimento)->format('d/m/Y') : '—',
                    'data_pagamento' => $l->data_pagamento ? \Carbon\Carbon::parse($l->data_pagamento)->format('d/m/Y') : null,
                ];
            });

        // Resumo Financeiro
        $resumoFinanceiro = [
            'total_pago' => (float) $lancamentos->where('status', 'pago')->sum('valor'),
            'total_pendente' => (float) $lancamentos->where('status', 'pendente')->sum('valor'),
            'qtd_pendentes' => $lancamentos->where('status', 'pendente')->count(),
            'qtd_pagos' => $lancamentos->where('status', 'pago')->count(),
        ];

        return Inertia::render('Associados/Show', [
            'associado' => [
                'id' => $associado->id,
                'user_id' => $associado->user_id,
                'nome' => $associado->nome,
                'cpf' => $associado->cpf,
                'email' => $associado->email,
                'nascimento' => $associado->nascimento ? $associado->nascimento->format('d/m/Y') : null,
                'status' => $associado->status,
                'matricula' => $associado->matricula,
                'data_adesao' => $associado->data_adesao ? $associado->data_adesao->format('d/m/Y') : null,
                'forma_pagamento_adesao' => $associado->forma_pagamento_adesao,
                'autoriza_desconto_folha' => $associado->autoriza_desconto_folha,
                'carteirinha_numero' => $associado->carteirinha_numero,
                'carteirinha_tipo' => $associado->carteirinha_tipo,
                'carteirinha_validade' => $associado->carteirinha_validade ? $associado->carteirinha_validade->format('d/m/Y') : null,
                'carteirinha_status' => $associado->carteirinha_status,
                'aprovado_por_nome' => $associado->aprovado_por_nome ?? $associado->aprovador?->name ?? 'Administrador',
                'aprovado_em' => $associado->aprovado_em ? $associado->aprovado_em->format('d/m/Y H:i') : null,
                'forma_pagamento_aprovacao' => $associado->forma_pagamento_aprovacao,
                'comprovante_pix_url' => $associado->comprovante_pix_path ? tenant_asset($associado->comprovante_pix_path) : null,
                'detalhes_financeiro_aprovacao' => $associado->detalhes_financeiro_aprovacao,
                'dependentes' => $associado->dependentes->map(function ($dep) {
                    return [
                        'id' => $dep->id,
                        'nome' => $dep->nome,
                        'cpf' => $dep->cpf,
                        'rg' => $dep->rg,
                        'cartao_sus' => $dep->cartao_sus,
                        'data_nascimento' => $dep->data_nascimento ? $dep->data_nascimento->format('Y-m-d') : null,
                        'data_nascimento_formatada' => $dep->data_nascimento ? $dep->data_nascimento->format('d/m/Y') : null,
                        'parentesco' => $dep->parentesco,
                        'tipo_dependencia' => $dep->tipo_dependencia,
                        'autoriza_acesso' => $dep->autoriza_acesso,
                        'email' => $dep->email,
                        'status' => $dep->status,
                        'access_token' => $dep->getMagicLinkToken(),
                    ];
                }),
                'enderecos' => $associado->enderecos,
                'telefones' => $associado->telefones,
                'documentos' => $associado->documentos->map(fn($doc) => [
                    'id' => $doc->id,
                    'titulo' => $doc->titulo,
                    'tipo' => $doc->tipo,
                    'status' => $doc->status,
                    'motivo_rejeicao' => $doc->motivo_rejeicao,
                    'download_url' => route('associados.documentos.download', ['associado' => $associado->id, 'documento' => $doc->id]),
                ]),
            ],
            'agendamentos' => $agendamentos,
            'lancamentos' => $lancamentos,
            'resumoFinanceiro' => $resumoFinanceiro,
        ]);
    }

    public function edit(Associado $associado)
    {
        $this->authorize('update', $associado);
        $associado->load('documentos');
        return Inertia::render('Associados/Form', [
            'associado' => $associado
        ]);
    }

    public function update(UpdateAssociadoRequest $request, Associado $associado)
    {
        $this->authorize('update', $associado);

        $associado->update($request->validated());

        return Redirect::route('associados.index')->with('success', 'Associado atualizado com sucesso!');
    }

    public function destroy(Associado $associado)
    {
        $this->authorize('delete', $associado);

        $associado->delete();

        return Redirect::route('associados.index')->with('success', 'Associado excluído com sucesso!');
    }
}
