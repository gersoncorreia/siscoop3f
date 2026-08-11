<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
        $this->authorize('viewAny', Associado::class);

        return Inertia::render('Associados/Index', [
            'associados' => Associado::latest()->paginate(10)->through(fn($a) => [
                'id' => $a->id,
                'nome' => $a->nome,
                'email' => $a->email,
                'matricula' => $a->matricula,
                'status' => $a->status,
                'cpf_masked' => $a->cpf ? substr($a->cpf, 0, 3) . '.***.***-' . substr($a->cpf, -2) : null,
                'data_adesao' => $a->data_adesao,
                'created_at' => $a->created_at->format('d/m/Y'),
            ])
        ]);
    }

    public function create()
    {
        $this->authorize('create', Associado::class);
        return Inertia::render('Associados/Form');
    }

    public function store(StoreAssociadoRequest $request)
    {
        $this->authorize('create', Associado::class);

        Associado::create($request->validated());

        return Redirect::route('associados.index')->with('success', 'Associado criado com sucesso!');
    }

    public function edit(Associado $associado)
    {
        $this->authorize('update', $associado);
        $associado->load('documentos');
        return Inertia::render('Associados/Form', [
            'associado' => $associado
        ]);
    }

    public function update(UpdateAssociadoRequest $request, Associado $associado)
    {
        $this->authorize('update', $associado);

        $associado->update($request->validated());

        return Redirect::route('associados.index')->with('success', 'Associado atualizado com sucesso!');
    }

    public function destroy(Associado $associado)
    {
        $this->authorize('delete', $associado);

        $associado->delete();

        return Redirect::route('associados.index')->with('success', 'Associado excluído com sucesso!');
    }
}
