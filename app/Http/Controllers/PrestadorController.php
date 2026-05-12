<?php

namespace App\Http\Controllers;

use App\Models\Prestador;
use App\Models\User;
use App\Http\Requests\Saude\PrestadorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $data = $request->validated();
        
        $userId = null;
        if (!empty($data['email']) && !empty($data['password'])) {
            $user = User::create([
                'name' => $data['nome'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole('Prestador');
            $userId = $user->id;
        }

        Prestador::create([
            'tipo' => $data['tipo'],
            'nome' => $data['nome'],
            'especialidades' => $data['especialidades'] ?? null,
            'endereco' => $data['endereco'] ?? null,
            'user_id' => $userId,
        ]);

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
