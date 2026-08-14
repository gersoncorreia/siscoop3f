<?php

namespace App\Services;

use App\Models\FaturaPrestador;
use App\Models\SolicitacaoServico;
use Illuminate\Database\Eloquent\Collection;

class SaudeService
{
    public function getAutorizacoesPendentes(): Collection
    {
        return SolicitacaoServico::with(['associado', 'prestador', 'servico'])
            ->orderByRaw("CASE WHEN status = 'pendente_autorizacao' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function aprovarAutorizacao(SolicitacaoServico $solicitacao): void
    {
        $solicitacao->update(['status' => 'aprovado_cooperativa']);
    }

    public function rejeitarAutorizacao(SolicitacaoServico $solicitacao, string $motivo): void
    {
        $solicitacao->update([
            'status' => 'negado',
            'motivo_recusa' => $motivo
        ]);
    }

    public function getFaturas(): Collection
    {
        return FaturaPrestador::with(['prestador'])
            ->orderByRaw("CASE WHEN status IN ('enviada', 'em_auditoria') THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function auditarFatura(FaturaPrestador $fatura): void
    {
        $fatura->update(['status' => 'em_auditoria']);
    }

    public function pagarFatura(FaturaPrestador $fatura): void
    {
        $fatura->update([
            'status' => 'paga',
            'data_pagamento' => now()
        ]);
    }
}
