<?php

namespace App\Http\Controllers\Admin;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;

use App\Models\Coparticipacao;
use App\Models\Reembolso;
use App\Http\Requests\Admin\StoreCoparticipacaoRequest;
use App\Http\Requests\Admin\UpdateCoparticipacaoRequest;
use Illuminate\Support\Facades\Redirect;

class CoparticipacaoController extends Controller
{
    protected CooperativeService $cooperativeService;

    public function __construct(CooperativeService $cooperativeService)
    {
        $this->cooperativeService = $cooperativeService;
    }

    public function store(StoreCoparticipacaoRequest $request, Reembolso $reembolso)
    {
        $validated = $request->validated();

        $this->cooperativeService->criarCoparticipacao($reembolso->id, $validated);

        return Redirect::back()->with('success', 'Coparticipação registrada com sucesso!');
    }

    public function update(UpdateCoparticipacaoRequest $request, Coparticipacao $coparticipacao)
    {
        $validated = $request->validated();

        $this->cooperativeService->atualizarCoparticipacao($coparticipacao->id, $validated);

        return Redirect::back()->with('success', 'Status da coparticipação atualizado!');
    }

    public function destroy(Coparticipacao $coparticipacao)
    {
        $this->cooperativeService->excluirCoparticipacao($coparticipacao->id);
        return Redirect::back()->with('success', 'Coparticipação excluída!');
    }
}
