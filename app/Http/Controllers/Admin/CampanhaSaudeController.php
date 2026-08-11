<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampanhaSaude;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CampanhaSaudeController extends Controller
{
    public function index(Request $request)
    {
        $query = CampanhaSaude::query();

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', $request->boolean('ativo'));
        }

        if ($request->boolean('vigentes')) {
            $query->vigentes();
        }

        $campanhas = $query->orderBy('data_inicio', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Campanhas/Index', [
            'campanhas' => $campanhas,
            'tipos' => CampanhaSaude::tipos(),
            'publicosAlvo' => CampanhaSaude::publicosAlvo(),
            'filters' => $request->only(['tipo', 'ativo', 'vigentes'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'tipo' => 'required|in:' . implode(',', array_keys(CampanhaSaude::tipos())),
            'publico_alvo' => 'required|in:' . implode(',', array_keys(CampanhaSaude::publicosAlvo())),
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'ativo' => 'boolean',
            'imagem' => 'nullable|image|max:2048',
            'meta_participantes' => 'nullable|integer|min:1',
            'notificar_membros' => 'boolean',
        ]);

        if ($request->hasFile('imagem')) {
            $path = $request->file('imagem')->store('campanhas', 'tenant');
            $validated['imagem_path'] = $path;
        }

        $validated['ativo'] = $request->boolean('ativo', true);
        $validated['notificar_membros'] = $request->boolean('notificar_membros', false);
        
        CampanhaSaude::create($validated);

        return redirect()->route('saude.campanhas.index')->with('success', 'Campanha criada com sucesso.');
    }

    public function update(Request $request, CampanhaSaude $campanha)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'tipo' => 'required|in:' . implode(',', array_keys(CampanhaSaude::tipos())),
            'publico_alvo' => 'required|in:' . implode(',', array_keys(CampanhaSaude::publicosAlvo())),
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'ativo' => 'boolean',
            'imagem' => 'nullable|image|max:2048',
            'meta_participantes' => 'nullable|integer|min:1',
            'notificar_membros' => 'boolean',
        ]);

        if ($request->hasFile('imagem')) {
            if ($campanha->imagem_path) {
                Storage::disk('tenant')->delete($campanha->imagem_path);
            }
            $path = $request->file('imagem')->store('campanhas', 'tenant');
            $validated['imagem_path'] = $path;
        }

        $validated['ativo'] = $request->boolean('ativo', $campanha->ativo);
        $validated['notificar_membros'] = $request->boolean('notificar_membros', $campanha->notificar_membros);

        $campanha->update($validated);

        return redirect()->route('saude.campanhas.index')->with('success', 'Campanha atualizada com sucesso.');
    }

    public function destroy(CampanhaSaude $campanha)
    {
        if ($campanha->imagem_path) {
            Storage::disk('tenant')->delete($campanha->imagem_path);
        }

        $campanha->delete();

        return redirect()->route('saude.campanhas.index')->with('success', 'Campanha excluída com sucesso.');
    }

    public function toggleAtivo(CampanhaSaude $campanha)
    {
        $campanha->update(['ativo' => !$campanha->ativo]);

        $status = $campanha->ativo ? 'ativada' : 'desativada';
        return back()->with('success', "Campanha {$status} com sucesso.");
    }
}
