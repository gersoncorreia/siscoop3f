<?php

namespace App\Http\Controllers;

use App\Models\ContratoEntidade;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ContratoCentralController extends Controller
{
    public function index()
    {
        $contratos = ContratoEntidade::with(['contratante', 'prestador'])->get();
        $tenants = Tenant::all();

        return Inertia::render('MasterAdmin/Contratos', [
            'contratos' => $contratos,
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_contratante_id' => ['required', \Illuminate\Validation\Rule::exists('central.tenants', 'id')],
            'tenant_prestador_id' => ['required', \Illuminate\Validation\Rule::exists('central.tenants', 'id'), 'different:tenant_contratante_id'],
            'status' => 'required|in:pendente,ativo,suspenso,encerrado',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'descricao' => 'nullable|string',
        ]);

        ContratoEntidade::create($validated);

        return Redirect::route('master-admin.contratos.index')->with('success', 'Contrato registrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $contrato = ContratoEntidade::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pendente,ativo,suspenso,encerrado',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'descricao' => 'nullable|string',
        ]);

        $contrato->update($validated);

        return Redirect::route('master-admin.contratos.index')->with('success', 'Contrato atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $contrato = ContratoEntidade::findOrFail($id);
        $contrato->delete();

        return Redirect::route('master-admin.contratos.index')->with('success', 'Contrato removido com sucesso!');
    }
}
