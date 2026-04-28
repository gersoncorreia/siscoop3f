<?php

namespace App\Http\Controllers;

use App\Models\Prestador;
use App\Http\Requests\Saude\PrestadorRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestadorController extends Controller
{
    public function index()
    {
        return Inertia::render('Saude/Prestadores/Index', [
            'prestadores' => Prestador::latest('created_at')->get()
        ]);
    }

    public function store(PrestadorRequest $request)
    {
        Prestador::create($request->validated());

        return redirect()->back()->with('success', 'Prestador cadastrado com sucesso!');
    }

    public function update(PrestadorRequest $request, Prestador $prestador)
    {
        $prestador->update($request->validated());

        return redirect()->back()->with('success', 'Prestador atualizado com sucesso!');
    }

    public function destroy(Prestador $prestador)
    {
        $prestador->delete();

        return redirect()->back()->with('success', 'Prestador excluído com sucesso!');
    }
}
