<?php

namespace App\Http\Controllers;

use App\Models\Coparticipacao;
use App\Models\Reembolso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CoparticipacaoController extends Controller
{
    public function store(Request $request, Reembolso $reembolso)
    {
        $validated = $request->validate([
            'valor_total' => 'required|numeric',
            'percentual' => 'required|numeric|min:0|max:100',
            'status' => 'required|string'
        ]);

        $reembolso->coparticipacoes()->create($validated);

        return Redirect::back()->with('success', 'Coparticipação registrada com sucesso!');
    }

    public function update(Request $request, Coparticipacao $coparticipacao)
    {
        $validated = $request->validate([
            'status' => 'required|string'
        ]);

        $coparticipacao->update($validated);

        return Redirect::back()->with('success', 'Status da coparticipação atualizado!');
    }

    public function destroy(Coparticipacao $coparticipacao)
    {
        $coparticipacao->delete();
        return Redirect::back()->with('success', 'Coparticipação excluída!');
    }
}
