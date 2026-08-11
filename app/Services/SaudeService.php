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
            ->orderByRaw("FIELD(status, 'pendente_autorizacao') DESC")
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
            ->orderByRaw("FIELD(status, 'enviada', 'em_auditoria') DESC")
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
