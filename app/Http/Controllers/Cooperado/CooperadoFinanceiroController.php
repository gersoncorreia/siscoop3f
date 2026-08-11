<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Contracts\AssociadoRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CooperadoFinanceiroController extends Controller
{
    protected AssociadoRepositoryInterface $associados;

    public function __construct(AssociadoRepositoryInterface $associados)
    {
        $this->associados = $associados;
    }

    /**
     * Extrato financeiro pessoal
     */
    public function index(Request $request): Response
    {
        abort_if(auth()->user()->hasRole('Dependente'), 403, 'Acesso restrito ao titular.');
        
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $query = $associado->lancamentosFinanceiros()->orderBy('data_vencimento', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lancamentos = $query->paginate(15)->through(fn ($l) => [
            'id' => $l->id,
            'tipo' => $l->tipo,
            'valor' => (float) $l->valor,
            'status' => $l->status,
            'data_vencimento' => $l->data_vencimento,
            'created_at' => $l->created_at->format('d/m/Y'),
        ]);

        return Inertia::render('Cooperado/Financeiro', [
            'lancamentos' => $lancamentos,
            'filtroStatus' => $request->status,
        ]);
    }

    /**
     * Cotas de capital do cooperado
     */
    public function cotaCapital(): Response
    {
        abort_if(auth()->user()->hasRole('Dependente'), 403, 'Acesso restrito ao titular.');
        
        $associado = $this->associados->getActiveAssociadoForUser(auth()->user());

        $cotas = $associado->cotasCapital()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'valor_quota' => (float) $c->valor_quota,
                'qtd_quotas' => $c->qtd_quotas,
                'saldo' => (float) $c->saldo,
                'tipo_movimento' => $c->tipo_movimento,
                'created_at' => $c->created_at->format('d/m/Y'),
            ]);

        $saldoTotal = $associado->cotasCapital->sum('saldo');

        return Inertia::render('Cooperado/Cotas', [
            'cotas' => $cotas,
            'saldoTotal' => (float) $saldoTotal,
        ]);
    }
}
