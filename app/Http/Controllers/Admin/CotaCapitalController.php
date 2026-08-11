<?php

namespace App\Http\Controllers\Admin;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;

use App\Models\CotaCapital;
use App\Models\Associado;
use App\Http\Requests\Admin\StoreCotaCapitalRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class CotaCapitalController extends Controller
{
    protected CooperativeService $cooperativeService;

    public function __construct(CooperativeService $cooperativeService)
    {
        $this->cooperativeService = $cooperativeService;
    }

    public function index()
    {
        Gate::authorize('financeiro.view');

        return Inertia::render('Financeiro/Capital/Index', [
            'cotas' => CotaCapital::with('associado')->latest()->paginate(15),
            'associados' => Associado::select('id', 'nome')->get()
        ]);
    }

    public function store(StoreCotaCapitalRequest $request)
    {
        Gate::authorize('financeiro.create');

        $validated = $request->validated();

        $this->cooperativeService->criarCotaCapital($validated);

        return Redirect::back()->with('success', 'Cota de capital registrada!');
    }

    public function destroy(CotaCapital $cotaCapital)
    {
        Gate::authorize('financeiro.delete');

        $this->cooperativeService->excluirCotaCapital($cotaCapital->id);
        return Redirect::back()->with('success', 'Registro de capital excluído!');
    }
}
