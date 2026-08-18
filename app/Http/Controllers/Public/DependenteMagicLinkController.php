<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Dependente;
use App\Models\Agendamento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class DependenteMagicLinkController extends Controller
{
    /**
     * Exibe o formulário de desafio dos 5 primeiros dígitos do CPF para acessar o Link Mágico
     */
    public function showForm(string $token)
    {
        $dependente = Dependente::where('access_token', $token)->first();

        if (!$dependente) {
            abort(404, 'Link mágico inválido ou expirado.');
        }

        $tenant = tenant();

        return Inertia::render('Auth/DependenteMagicLink', [
            'token' => $token,
            'dependenteNome' => $dependente->nome,
            'associadoNome' => $dependente->associado?->nome ?? 'Titular',
            'tenant' => [
                'nome' => $tenant->nome ?? 'Fundação Santa Casa da Amazônia',
                'tipo' => $tenant->tipo ?? 'fundacao',
            ],
        ]);
    }

    /**
     * Valida se os 5 dígitos do CPF inseridos conferem com os 5 primeiros dígitos do CPF do dependente
     */
    public function validarCpf(string $token, Request $request)
    {
        $request->validate([
            'cpf_5_digitos' => 'required|string|size:5',
        ], [
            'cpf_5_digitos.required' => 'Informe os 5 primeiros dígitos do CPF.',
            'cpf_5_digitos.size' => 'O código deve ter exatamente 5 dígitos.',
        ]);

        $dependente = Dependente::where('access_token', $token)->first();

        if (!$dependente) {
            return Redirect::back()->withErrors(['cpf_5_digitos' => 'Link mágico inválido ou expirado.']);
        }

        // Limpar CPF do dependente cadastrado (apenas dígitos)
        $cleanCpf = preg_replace('/\D/', '', $dependente->cpf ?? '');
        $primeros5Digitos = substr($cleanCpf, 0, 5);

        $inputDigitos = preg_replace('/\D/', '', $request->input('cpf_5_digitos'));

        if ($inputDigitos !== $primeros5Digitos) {
            return Redirect::back()->withErrors([
                'cpf_5_digitos' => 'Os 5 primeiros dígitos informados não conferem com o CPF do dependente.'
            ]);
        }

        // Autenticar sessão do dependente
        session([
            'dependente_autenticado_id' => $dependente->id,
            'dependente_autenticado_token' => $token,
        ]);

        return Redirect::route('dependente.portal.index');
    }

    /**
     * Encerra a sessão do dependente
     */
    public function logout()
    {
        session()->forget(['dependente_autenticado_id', 'dependente_autenticado_token']);
        return Redirect::route('login');
    }
}
