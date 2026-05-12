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
        if (!empty($data['email'])) {
            $temporaryPassword = str()->random(8);

            $user = User::create([
                'name' => $data['nome'],
                'email' => $data['email'],
                'password' => Hash::make($temporaryPassword),
                'email_verified_at' => now(),
                'has_temporary_password' => true,
            ]);
            $user->assignRole('Prestador');
            $userId = $user->id;

            try {
                $user->notify(new \App\Notifications\PrestadorCriadoNotification(
                    $data['nome'],
                    $data['email'],
                    $temporaryPassword
                ));
            } catch (\Exception $ne) {
                \Illuminate\Support\Facades\Log::error("Erro ao enviar notificação de prestador: " . $ne->getMessage());
            }
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
