<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Prestador;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgendamentoController extends Controller
{
    public function index()
    {
        $agendamentos = Agendamento::where('user_id', auth()->id())
            ->with('prestador')
            ->orderBy('data_hora', 'desc')
            ->get();

        return Inertia::render('Cooperado/Agendamentos/Index', [
            'agendamentos' => $agendamentos,
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

        return Inertia::render('Cooperado/Agendamentos/Criar', [
            'prestadores' => $prestadores,
            'tabelaServicos' => $tabelaServicos,
            'selectedServico' => $selectedServico,
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
            'observacoes'       => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'pendente';

        if (!empty($validated['tabela_servico_id'])) {
            $servico = \App\Models\TabelaServico::find($validated['tabela_servico_id']);
            if ($servico) {
                $validated['valor_cobrado'] = $servico->valor_base;
            }
        }

        $agendamento = Agendamento::create($validated);

        // Disparar Notificação para o Usuário
        $user = auth()->user();
        if ($user) {
            $tipoLabel = ucfirst($validated['tipo']);
            $user->notify(new \App\Notifications\AppDatabaseNotification(
                'Agendamento Solicitado',
                "Seu agendamento de {$tipoLabel} ({$validated['especialidade']}) foi registrado com sucesso.",
                route('cooperado.agendamentos.index'),
                'CalendarDaysIcon'
            ));
        }

        return redirect()->route('cooperado.agendamentos.index')
            ->with('success', 'Agendamento solicitado com sucesso! Aguarde confirmação.');
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
