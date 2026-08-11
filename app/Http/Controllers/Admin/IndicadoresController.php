<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Associado;
use App\Models\CampanhaSaude;
use App\Models\LancamentoFinanceiro;
use App\Models\SolicitacaoInternacao;
use App\Models\TabelaServico;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class IndicadoresController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();
        $mes = $hoje->month;
        $ano = $hoje->year;

        // Fetch Total members
        $totalMembros = Associado::count();
        
        // Agendamentos percentage
        $agendamentosMes = Agendamento::mesAtual()->count();
        $agendamentosRealizadosMes = Agendamento::mesAtual()->realizados()->count();
        $taxaAtendimento = $agendamentosMes > 0 
            ? round(($agendamentosRealizadosMes / $agendamentosMes) * 100, 2) 
            : 0;

        $stats = [
            // Membros
            'totalMembros' => $totalMembros,
            'membrosAtivos' => Associado::where('status', 'ativo')->count(),
            'novasAdesoesMes' => Associado::whereMonth('data_adesao', $mes)->whereYear('data_adesao', $ano)->count(),
            'cancelamentosMes' => Associado::where('status', 'inativo')->whereMonth('updated_at', $mes)->whereYear('updated_at', $ano)->count(),
            
            // Agendamentos
            'agendamentosHoje' => Agendamento::hoje()->count(),
            'agendamentosMes' => $agendamentosMes,
            'agendamentosRealizadosMes' => $agendamentosRealizadosMes,
            'taxaAtendimento' => $taxaAtendimento,
            
            // Internações
            'internacoesEmAnalise' => SolicitacaoInternacao::where('status', 'analise')->count(),
            
            // Financeiro
            'receitaMes' => LancamentoFinanceiro::where('tipo', 'entrada')->whereMonth('data_vencimento', $mes)->whereYear('data_vencimento', $ano)->sum('valor'),
            'despesaMes' => LancamentoFinanceiro::where('tipo', 'saida')->whereMonth('data_vencimento', $mes)->whereYear('data_vencimento', $ano)->sum('valor'),
            
            // Campanhas
            'campanhasAtivas' => CampanhaSaude::ativas()->vigentes()->count(),
        ];

        // Evolução de membros últimos 6 meses
        $evolucaoMembros = [];
        for ($i = 5; $i >= 0; $i--) {
            $data = Carbon::now()->subMonths($i);
            $mesData = $data->month;
            $anoData = $data->year;
            
            $novos = Associado::whereMonth('data_adesao', $mesData)->whereYear('data_adesao', $anoData)->count();
            $cancelamentos = Associado::where('status', 'inativo')->whereMonth('updated_at', $mesData)->whereYear('updated_at', $anoData)->count();
            $totalMes = Associado::where('data_adesao', '<=', $data->endOfMonth())->where(function($query) use ($data) {
                $query->where('status', 'ativo')
                      ->orWhere(function($q) use ($data) {
                          $q->where('status', 'inativo')
                            ->where('updated_at', '>', $data->endOfMonth());
                      });
            })->count();

            $evolucaoMembros[] = [
                'label' => $data->format('M/y'),
                'novos' => $novos,
                'cancelamentos' => $cancelamentos,
                'total' => $totalMes
            ];
        }

        // Receita por categoria de serviço
        // Simulando a partir da relação Agendamento -> TabelaServico
        $receitaPorCategoria = DB::table('agendamentos')
            ->join('tabela_servicos', 'agendamentos.tabela_servico_id', '=', 'tabela_servicos.id')
            ->whereMonth('agendamentos.created_at', $mes)
            ->whereYear('agendamentos.created_at', $ano)
            ->where('agendamentos.status', 'realizado')
            ->select('tabela_servicos.categoria as categoria', DB::raw('SUM(tabela_servicos.valor_base) as total'))
            ->groupBy('tabela_servicos.categoria')
            ->get();

        // Top 10 Serviços mais utilizados no mês
        $topServicos = Agendamento::with('tabelaServico')
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $ano)
            ->select('tabela_servico_id', DB::raw('count(*) as total_agendamentos'), DB::raw('sum(valor_cobrado) as receita'))
            ->groupBy('tabela_servico_id')
            ->orderByDesc('total_agendamentos')
            ->take(10)
            ->get()
            ->map(function ($a) {
                return [
                    'nome' => $a->tabelaServico?->nome ?? 'Serviço Geral',
                    'total_agendamentos' => $a->total_agendamentos,
                    'receita' => (float) ($a->receita ?? 0)
                ];
            });

        return Inertia::render('Admin/Indicadores/Index', [
            'stats' => $stats,
            'evolucaoMembros' => $evolucaoMembros,
            'receitaPorCategoria' => $receitaPorCategoria,
            'topServicos' => $topServicos,
        ]);
    }
}
