<?php

namespace App\Http\Controllers;

use App\Models\Associado;
use App\Services\AdesaoService;
use App\Http\Requests\Adesao\SolicitarAdesaoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;

class AdesaoController extends Controller
{
    /**
     * Exibe o formulário público de adesão no tenant
     */
    public function create()
    {
        return Inertia::render('Adesao/Form');
    }

    /**
     * Processa os dados do formulário público de adesão
     */
    public function store(SolicitarAdesaoRequest $request, AdesaoService $adesaoService)
    {
        $associado = $adesaoService->processarAdesao($request->validated());

        return Redirect::route('adesao.success')->with('success', 'Cadastro realizado com sucesso! Aguarde a aprovação.');
    }

    /**
     * Exibe tela de sucesso após o cadastro público
     */
    public function success()
    {
        return Inertia::render('Adesao/Success');
    }

    /**
     * PAINEL ADMIN: Lista adesões pendentes
     */
    public function index()
    {
        Gate::authorize('associados.view');

        $pendentes = Associado::where('status', 'analise_documental')->latest()->paginate(10);

        return Inertia::render('Associados/Adesoes/Index', [
            'pendentes' => $pendentes
        ]);
    }

    /**
     * PAINEL ADMIN: Aprova uma adesão
     */
    public function aprovar(Associado $associado, AdesaoService $adesaoService)
    {
        Gate::authorize('associados.update');

        try {
            $adesaoService->aprovarAdesao($associado);
            return Redirect::back()->with('success', 'Adesão aprovada com sucesso!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao aprovar adesão no Controller: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return Redirect::back()->with('error', 'Erro ao aprovar adesão: ' . $e->getMessage());
        }
    }

    /**
     * PAINEL ADMIN: Rejeita uma adesão
     */
    public function rejeitar(Associado $associado)
    {
        Gate::authorize('associados.delete');

        $associado->update(['status' => 'recusado']);

        return Redirect::back()->with('success', 'Adesão recusada.');
    }
}
