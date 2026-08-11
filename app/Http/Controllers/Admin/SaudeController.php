<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\FaturaPrestador;
use App\Models\SolicitacaoServico;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

use App\Http\Requests\Saude\RejeitarAutorizacaoRequest;
use App\Services\SaudeService;

class SaudeController extends Controller
{
    protected SaudeService $saudeService;

    public function __construct(SaudeService $saudeService)
    {
        $this->saudeService = $saudeService;
    }

    /**
     * Lista de Autorizações Pendentes para o Hélio/Gerência aprovar.
     */
    public function autorizacoesPendentes()
    {
        Gate::authorize('saude.view');
        
        $solicitacoes = $this->saudeService->getAutorizacoesPendentes()
            ->map(fn($s) => [
                'id' => $s->id,
                'paciente' => $s->associado->nome ?? 'N/A',
                'matricula' => str_pad((string)($s->associado->id ?? 0), 4, '0', STR_PAD_LEFT),
                'prestador' => $s->prestador->nome ?? 'N/A',
                'servico' => $s->servico->nome ?? 'Outros',
                'valor' => $s->valor_cobrado,
                'observacoes' => $s->observacoes,
                'status' => $s->status,
                'data' => $s->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Admin/Saude/Autorizacoes', [
            'solicitacoes' => $solicitacoes
        ]);
    }

    public function aprovarAutorizacao(SolicitacaoServico $solicitacao)
    {
        Gate::authorize('saude.update');
        if ($solicitacao->status !== 'pendente_autorizacao') {
            return back()->with('error', 'Esta solicitação não está pendente.');
        }

        $this->saudeService->aprovarAutorizacao($solicitacao);

        return back()->with('success', 'Solicitação aprovada com sucesso. O prestador e o cooperado foram notificados.');
    }

    public function rejeitarAutorizacao(RejeitarAutorizacaoRequest $request, SolicitacaoServico $solicitacao)
    {
        Gate::authorize('saude.update');

        $this->saudeService->rejeitarAutorizacao($solicitacao, $request->validated()['motivo']);

        return back()->with('success', 'Solicitação negada.');
    }

    /**
     * Faturamento do Dia 05
     */
    public function faturas()
    {
        Gate::authorize('saude.view');
        
        $faturas = $this->saudeService->getFaturas()
            ->map(fn($f) => [
                'id' => $f->id,
                'prestador' => $f->prestador->nome ?? 'N/A',
                'mes_referencia' => $f->mes_referencia,
                'valor_total' => $f->valor_total,
                'status' => $f->status,
                'arquivo_guia' => $f->arquivo_guia,
                'data_envio' => $f->created_at->format('d/m/Y H:i'),
                'data_pagamento' => $f->data_pagamento ? \Carbon\Carbon::parse($f->data_pagamento)->format('d/m/Y') : null,
            ]);

        return Inertia::render('Admin/Saude/Faturas', [
            'faturas' => $faturas
        ]);
    }

    public function auditarFatura(FaturaPrestador $fatura)
    {
        Gate::authorize('saude.update');
        $this->saudeService->auditarFatura($fatura);
        return back()->with('success', 'Fatura em auditoria.');
    }

    public function pagarFatura(FaturaPrestador $fatura)
    {
        Gate::authorize('saude.update');
        $this->saudeService->pagarFatura($fatura);
        return back()->with('success', 'Fatura marcada como paga! (O pagamento real foi feito via banco).');
    }
}
