<?php

namespace App\Http\Controllers\Admin;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;

use App\Models\Convenio;
use App\Http\Requests\Admin\StoreConvenioRequest;
use App\Http\Requests\Admin\UpdateConvenioRequest;
use Inertia\Inertia;

class ConvenioController extends Controller
{
    protected CooperativeService $cooperativeService;

    public function __construct(CooperativeService $cooperativeService)
    {
        $this->cooperativeService = $cooperativeService;
    }

    public function index()
    {
        $convenios = Convenio::orderBy('nome_empresa')->get();
        return Inertia::render('Associados/Convenios/Index', [
            'convenios' => $convenios
        ]);
    }

    public function store(StoreConvenioRequest $request)
    {
        $validated = $request->validated();

        $this->cooperativeService->criarConvenio($validated);
        return redirect()->back()->with('success', 'Convênio criado com sucesso.');
    }

    public function update(UpdateConvenioRequest $request, Convenio $convenio)
    {
        $validated = $request->validated();

        $this->cooperativeService->atualizarConvenio($convenio->id, $validated);
        return redirect()->back()->with('success', 'Convênio atualizado com sucesso.');
    }

    public function destroy(Convenio $convenio)
    {
        $this->cooperativeService->excluirConvenio($convenio->id);
        return redirect()->back()->with('success', 'Convênio excluído com sucesso.');
    }
}
