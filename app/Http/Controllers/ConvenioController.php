<?php

namespace App\Http\Controllers;

use App\Models\Convenio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConvenioController extends Controller
{
    public function index()
    {
        $convenios = Convenio::orderBy('nome_empresa')->get();
        return Inertia::render('Associados/Convenios/Index', [
            'convenios' => $convenios
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_empresa' => 'required|string|max:255',
            'ramo' => 'nullable|string|max:255',
            'desconto' => 'nullable|string|max:255',
            'contato' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean'
        ]);

        Convenio::create($validated);
        return redirect()->back()->with('success', 'Convênio criado com sucesso.');
    }

    public function update(Request $request, Convenio $convenio)
    {
        $validated = $request->validate([
            'nome_empresa' => 'required|string|max:255',
            'ramo' => 'nullable|string|max:255',
            'desconto' => 'nullable|string|max:255',
            'contato' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean'
        ]);

        $convenio->update($validated);
        return redirect()->back()->with('success', 'Convênio atualizado com sucesso.');
    }

    public function destroy(Convenio $convenio)
    {
        $convenio->delete();
        return redirect()->back()->with('success', 'Convênio excluído com sucesso.');
    }
}
