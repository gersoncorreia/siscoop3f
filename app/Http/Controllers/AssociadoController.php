<?php

namespace App\Http\Controllers;

use App\Models\Associado;
use App\Http\Requests\Associado\StoreAssociadoRequest;
use App\Http\Requests\Associado\UpdateAssociadoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class AssociadoController extends Controller
{
    public function index()
    {
        Gate::authorize('associados.view');

        return Inertia::render('Associados/Index', [
            'associados' => Associado::latest()->paginate(10)
        ]);
    }

    public function create()
    {
        Gate::authorize('associados.create');
        return Inertia::render('Associados/Form');
    }

    public function store(StoreAssociadoRequest $request)
    {
        Associado::create($request->validated());

        return Redirect::route('associados.index')->with('success', 'Associado criado com sucesso!');
    }

    public function edit(Associado $associado)
    {
        Gate::authorize('associados.update');
        $associado->load('documentos');
        return Inertia::render('Associados/Form', [
            'associado' => $associado
        ]);
    }

    public function update(UpdateAssociadoRequest $request, Associado $associado)
    {
        $associado->update($request->validated());

        return Redirect::route('associados.index')->with('success', 'Associado atualizado com sucesso!');
    }

    public function destroy(Associado $associado)
    {
        Gate::authorize('associados.delete');

        $associado->delete();

        return Redirect::route('associados.index')->with('success', 'Associado excluído com sucesso!');
    }
}
