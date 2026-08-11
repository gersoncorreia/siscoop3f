<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RelatorioAssociadosExport;
use App\Exports\RelatorioFinanceiroExport;
use App\Exports\RelatorioDependentesExport;
use App\Exports\RelatorioAdesoesExport;
use App\Exports\RelatorioAgendamentosExport;
use App\Exports\RelatorioPrestadoresExport;

class RelatorioController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('relatorios.view'), 403, 'Acesso negado aos relatórios.');
        
        return Inertia::render('Relatorios/Index');
    }
    
    public function exportAssociados(Request $request) 
    {
        abort_unless(auth()->user()->can('relatorios.view'), 403);
        
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        
        $filename = 'associados_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new RelatorioAssociadosExport($dataInicio, $dataFim), $filename);
    }
    
    public function exportFinanceiro(Request $request) 
    {
        abort_unless(auth()->user()->can('relatorios.view') || auth()->user()->can('contabil.export'), 403);
        
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $status = $request->input('status');
        
        $filename = 'financeiro_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new RelatorioFinanceiroExport($dataInicio, $dataFim, $status), $filename);
    }
    
    public function exportDependentes(Request $request) 
    {
        abort_unless(auth()->user()->can('relatorios.view'), 403);
        
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        
        $filename = 'dependentes_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new RelatorioDependentesExport($dataInicio, $dataFim), $filename);
    }
    
    public function exportAdesoes(Request $request) 
    {
        abort_unless(auth()->user()->can('relatorios.view'), 403);
        
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        
        $filename = 'adesoes_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new RelatorioAdesoesExport($dataInicio, $dataFim), $filename);
    }

    public function exportAgendamentos(Request $request)
    {
        abort_unless(auth()->user()->can('relatorios.view'), 403);

        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $status = $request->input('status');

        $filename = 'agendamentos_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new RelatorioAgendamentosExport($dataInicio, $dataFim, $status), $filename);
    }

    public function exportPrestadores()
    {
        abort_unless(auth()->user()->can('relatorios.view'), 403);

        $filename = 'prestadores_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new RelatorioPrestadoresExport(), $filename);
    }
}
