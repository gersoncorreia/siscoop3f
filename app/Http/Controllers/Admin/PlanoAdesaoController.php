<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanoAdesao;
use App\Models\TabelaServico;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PlanoAdesaoController extends Controller
{
    public function index()
    {
        $planos = PlanoAdesao::withCount(['associados as membros_ativos' => function($q) {
            $q->where('status', 'ativo');
        }])->ordenado()->get();

        return Inertia::render('Admin/PlanosAdesao/Index', [
            'planos' => $planos,
            'tipos' => PlanoAdesao::tipos(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', Rule::in(array_keys(PlanoAdesao::tipos()))],
            'valor_adesao' => ['required', 'numeric', 'min:0'],
            'valor_mensalidade' => ['nullable', 'numeric', 'min:0'],
            'descricao' => ['nullable', 'string'],
            'max_dependentes' => ['nullable', 'integer', 'min:0'],
            'min_membros' => ['nullable', 'integer', 'min:1'],
            'beneficios' => ['nullable', 'array'],
            'ativo' => ['boolean'],
            'ordem' => ['nullable', 'integer'],
        ]);

        $validated['ativo'] = $validated['ativo'] ?? true;
        $validated['ordem'] = $validated['ordem'] ?? 0;

        PlanoAdesao::create($validated);

        return redirect()->back()->with('success', 'Plano criado com sucesso.');
    }

    public function update(Request $request, PlanoAdesao $planoAdesao)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', Rule::in(array_keys(PlanoAdesao::tipos()))],
            'valor_adesao' => ['required', 'numeric', 'min:0'],
            'valor_mensalidade' => ['nullable', 'numeric', 'min:0'],
            'descricao' => ['nullable', 'string'],
            'max_dependentes' => ['nullable', 'integer', 'min:0'],
            'min_membros' => ['nullable', 'integer', 'min:1'],
            'beneficios' => ['nullable', 'array'],
            'ativo' => ['boolean'],
            'ordem' => ['nullable', 'integer'],
        ]);

        $validated['ativo'] = $validated['ativo'] ?? true;
        $validated['ordem'] = $validated['ordem'] ?? 0;

        $planoAdesao->update($validated);

        return redirect()->back()->with('success', 'Plano atualizado com sucesso.');
    }

    public function destroy(PlanoAdesao $planoAdesao)
    {
        if ($planoAdesao->associados()->exists()) {
            return redirect()->back()->with('error', 'Não é possível excluir um plano que possui associados vinculados.');
        }

        $planoAdesao->delete();

        return redirect()->back()->with('success', 'Plano excluído com sucesso.');
    }

    public function precos(PlanoAdesao $planoAdesao)
    {
        $servicos = TabelaServico::ordenado()->get();
        $precosAtuais = $planoAdesao->precosServicos;

        return response()->json([
            'servicos' => $servicos,
            'precosAtuais' => $precosAtuais,
        ]);
    }

    public function updatePrecos(Request $request, PlanoAdesao $planoAdesao)
    {
        $validated = $request->validate([
            'precos' => ['array'],
            'precos.*.tabela_servico_id' => ['required', 'exists:tabela_servicos,id'],
            'precos.*.valor_personalizado' => ['nullable', 'numeric', 'min:0'],
            'precos.*.desconto_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'precos.*.incluso' => ['boolean'],
        ]);

        DB::transaction(function () use ($planoAdesao, $validated) {
            $planoAdesao->precosServicos()->delete();
            
            if (!empty($validated['precos'])) {
                $planoAdesao->precosServicos()->createMany($validated['precos']);
            }
        });

        return redirect()->back()->with('success', 'Preços do plano atualizados com sucesso.');
    }
}
