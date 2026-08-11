<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TabelaServico;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class TabelaServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = TabelaServico::query();

        if ($request->filled('categoria')) {
            $query->categoria($request->categoria);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%')
                  ->orWhere('codigo', 'like', '%' . $request->search . '%');
            });
        }

        $servicos = $query->ordenado()->paginate(15)->withQueryString();

        return Inertia::render('Admin/TabelaServicos/Index', [
            'servicos' => $servicos,
            'categorias' => TabelaServico::categorias(),
            'filters' => $request->only(['categoria', 'search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:255', 'unique:tabela_servicos,codigo'],
            'nome' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', Rule::in(array_keys(TabelaServico::categorias()))],
            'descricao' => ['nullable', 'string'],
            'valor_base' => ['required', 'numeric', 'min:0'],
            'ativo' => ['boolean'],
            'ordem' => ['nullable', 'integer'],
        ]);

        $validated['ativo'] = $validated['ativo'] ?? true;
        $validated['ordem'] = $validated['ordem'] ?? 0;

        TabelaServico::create($validated);

        return redirect()->back()->with('success', 'Serviço criado com sucesso.');
    }

    public function update(Request $request, TabelaServico $tabelaServico)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:255', Rule::unique('tabela_servicos')->ignore($tabelaServico->id)],
            'nome' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', Rule::in(array_keys(TabelaServico::categorias()))],
            'descricao' => ['nullable', 'string'],
            'valor_base' => ['required', 'numeric', 'min:0'],
            'ativo' => ['boolean'],
            'ordem' => ['nullable', 'integer'],
        ]);

        $validated['ativo'] = $validated['ativo'] ?? true;
        $validated['ordem'] = $validated['ordem'] ?? 0;

        $tabelaServico->update($validated);

        return redirect()->back()->with('success', 'Serviço atualizado com sucesso.');
    }

    public function destroy(TabelaServico $tabelaServico)
    {
        if ($tabelaServico->precosPlanos()->exists() || $tabelaServico->agendamentos()->exists()) {
            return redirect()->back()->with('error', 'Não é possível excluir o serviço pois ele possui vínculos.');
        }

        $tabelaServico->delete();

        return redirect()->back()->with('success', 'Serviço excluído com sucesso.');
    }

    public function toggleAtivo(TabelaServico $tabelaServico)
    {
        $tabelaServico->update(['ativo' => !$tabelaServico->ativo]);

        return redirect()->back()->with('success', 'Status do serviço atualizado com sucesso.');
    }
}
