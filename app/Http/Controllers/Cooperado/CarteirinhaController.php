<?php

namespace App\Http\Controllers\Cooperado;

use App\Http\Controllers\Controller;
use App\Models\Associado;
use App\Models\PlanoAdesao;
use Inertia\Inertia;

class CarteirinhaController extends Controller
{
    /**
     * Display the digital membership card for the authenticated member.
     */
    public function index()
    {
        $user = auth()->user();
        $associado = Associado::where('user_id', $user->id)->first()
            ?? $user->dependente?->associado;

        if (!$associado) {
            abort(404, 'Nenhum cadastro de associado vinculado a este usuário.');
        }

        $associado->load('planoAdesao', 'dependentes');

        // Get available plans for purchase
        $planosDisponiveis = PlanoAdesao::ativos()->ordenado()->get();

        return Inertia::render('Cooperado/Carteirinha', [
            'associado' => [
                'id' => $associado->id,
                'nome' => $associado->nome,
                'matricula' => $associado->matricula,
                'status' => $associado->status,
                'nascimento' => $associado->nascimento?->format('d/m/Y'),
                'data_adesao' => $associado->data_adesao?->format('d/m/Y'),
                'carteirinha_numero' => $associado->carteirinha_numero,
                'carteirinha_tipo' => $associado->carteirinha_tipo,
                'carteirinha_validade' => $associado->carteirinha_validade?->format('d/m/Y'),
                'carteirinha_status' => $associado->carteirinha_status,
                'carteirinha_emitida_em' => $associado->carteirinha_emitida_em?->format('d/m/Y H:i'),
                'carteirinha_valida' => $associado->isCarteirinhaValida(),
                'plano' => $associado->planoAdesao ? [
                    'id' => $associado->planoAdesao->id,
                    'nome' => $associado->planoAdesao->nome,
                    'tipo' => $associado->planoAdesao->tipo,
                    'tipo_label' => $associado->planoAdesao->tipo_label,
                ] : null,
                'total_dependentes' => $associado->dependentes->count(),
                'dependentes' => $associado->dependentes->map(fn($d) => [
                    'id' => $d->id,
                    'nome' => $d->nome,
                    'parentesco' => $d->parentesco ?? null,
                ]),
            ],
            'planosDisponiveis' => $planosDisponiveis->map(fn($p) => [
                'id' => $p->id,
                'nome' => $p->nome,
                'tipo' => $p->tipo,
                'tipo_label' => $p->tipo_label,
                'valor_adesao' => (float) $p->valor_adesao,
                'valor_mensalidade' => (float) $p->valor_mensalidade,
                'descricao' => $p->descricao,
            ]),
        ]);
    }

    /**
     * Request a new digital card or activate existing one.
     */
    public function solicitar()
    {
        $user = auth()->user();
        $associado = Associado::where('user_id', $user->id)->firstOrFail();

        $validated = request()->validate([
            'plano_adesao_id' => 'nullable|exists:planos_adesao,id',
            'tipo' => 'required|in:individual,familiar',
        ]);

        $associado->emitirCarteirinha(
            $validated['tipo'],
            $validated['plano_adesao_id'] ?? null
        );

        return redirect()->back()->with('success', 'Carteirinha digital emitida com sucesso!');
    }
}
