<?php

namespace App\Http\Controllers;

use App\Models\FaturaPrestador;
use App\Models\SolicitacaoServico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaudeController extends Controller
{
    /**
     * Lista de Autorizações Pendentes para o Hélio/Gerência aprovar.
     */
    public function autorizacoesPendentes()
    {
        $solicitacoes = SolicitacaoServico::with(['associado', 'prestador', 'servico'])
            ->orderByRaw("FIELD(status, 'pendente_autorizacao') DESC")
            ->orderBy('created_at', 'desc')
            ->get()
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
        if ($solicitacao->status !== 'pendente_autorizacao') {
            return back()->with('error', 'Esta solicitação não está pendente.');
        }

        $solicitacao->update(['status' => 'aprovado_cooperativa']);

        return back()->with('success', 'Solicitação aprovada com sucesso. O prestador e o cooperado foram notificados.');
    }

    public function rejeitarAutorizacao(Request $request, SolicitacaoServico $solicitacao)
    {
        $request->validate(['motivo' => 'required|string']);

        $solicitacao->update([
            'status' => 'negado',
            'motivo_recusa' => $request->motivo
        ]);

        return back()->with('success', 'Solicitação negada.');
    }

    /**
     * Faturamento do Dia 05
     */
    public function faturas()
    {
        $faturas = FaturaPrestador::with(['prestador'])
            ->orderByRaw("FIELD(status, 'enviada', 'em_auditoria') DESC")
            ->orderBy('created_at', 'desc')
            ->get()
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
        $fatura->update(['status' => 'em_auditoria']);
        return back()->with('success', 'Fatura em auditoria.');
    }

    public function pagarFatura(FaturaPrestador $fatura)
    {
        $fatura->update([
            'status' => 'paga',
            'data_pagamento' => now()
        ]);
        return back()->with('success', 'Fatura marcada como paga! (O pagamento real foi feito via banco).');
    }
}
