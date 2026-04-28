<?php

namespace App\Http\Controllers;

use App\Models\PlanoSaude;
use App\Http\Requests\Saude\PlanoSaudeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanoSaudeController extends Controller
{
    public function index()
    {
        return Inertia::render('Saude/Planos/Index', [
            'planos' => PlanoSaude::latest('created_at')->get()
        ]);
    }

    public function store(PlanoSaudeRequest $request)
    {
        PlanoSaude::create($request->validated());

        return redirect()->back()->with('success', 'Plano de saúde criado com sucesso!');
    }

    public function update(PlanoSaudeRequest $request, PlanoSaude $planoSaude)
    {
        $planoSaude->update($request->validated());

        return redirect()->back()->with('success', 'Plano de saúde atualizado com sucesso!');
    }

    public function destroy(PlanoSaude $planoSaude)
    {
        $planoSaude->delete();

        return redirect()->back()->with('success', 'Plano de saúde excluído com sucesso!');
    }
}
