<?php

namespace App\Http\Controllers\Admin;
use App\Http\Requests\Admin\UpdateReembolsoRequest;
use App\Http\Requests\Admin\StoreReembolsoRequest;
use App\Http\Requests\Admin\RejeitarReembolsoRequest;

use App\Services\CooperativeService;

use App\Http\Controllers\Controller;

use App\Models\Reembolso;
use App\Models\Associado;

use Inertia\Inertia;

class ReembolsoController extends Controller
{
    protected CooperativeService $cooperativeService;

    public function __construct(CooperativeService $cooperativeService)
    {
        $this->cooperativeService = $cooperativeService;
    }

    public function index()
    {
        $this->authorize('viewAny', Reembolso::class);

        return Inertia::render('Saude/Reembolsos/Index', [
            'reembolsos' => Reembolso::with('associado')->latest('created_at')->paginate(10),
            'associados' => Associado::select('id', 'nome')->get()
        ]);
    }

    public function store(StoreReembolsoRequest $request)
    {
        $this->authorize('create', Reembolso::class);

        $this->cooperativeService->criarReembolso($request->validated());

        return redirect()->back()->with('success', 'Solicitação de reembolso enviada!');
    }

    public function update(UpdateReembolsoRequest $request, Reembolso $reembolso)
    {
        $this->authorize('update', $reembolso);

        $this->cooperativeService->atualizarReembolso($reembolso->id, $request->validated());

        return redirect()->back()->with('success', 'Status do reembolso atualizado!');
    }

    public function destroy(Reembolso $reembolso)
    {
        $this->authorize('delete', $reembolso);

        $this->cooperativeService->excluirReembolso($reembolso->id);

        return redirect()->back()->with('success', 'Solicitação removida!');
    }

    public function aprovar(Reembolso $reembolso)
    {
        $this->authorize('update', $reembolso);

        $this->cooperativeService->aprovarReembolsoAdmin($reembolso->id);

        if ($reembolso->associado && $reembolso->associado->user) {
            $reembolso->associado->user->notify(new \App\Notifications\AppDatabaseNotification(
                'Reembolso Aprovado',
                "Seu pedido de reembolso no valor de R$ " . number_format($reembolso->valor_solicitado, 2, ',', '.') . " foi aprovado.",
                route('cooperado.reembolsos'),
                'CheckCircleIcon'
            ));
        }

        return redirect()->back()->with('success', 'Solicitação de reembolso aprovada!');
    }

    public function rejeitar(RejeitarReembolsoRequest $request, Reembolso $reembolso)
    {
        $this->authorize('update', $reembolso);

        $reembolso->update([
            'status' => 'rejeitado',
            'motivo_recusa' => $request->motivo_recusa,
        ]);

        if ($reembolso->associado && $reembolso->associado->user) {
            $reembolso->associado->user->notify(new \App\Notifications\AppDatabaseNotification(
                'Reembolso Rejeitado',
                "Seu pedido de reembolso foi rejeitado. Motivo: {$request->motivo_recusa}",
                route('cooperado.reembolsos'),
                'XCircleIcon'
            ));
        }

        return redirect()->back()->with('success', 'Solicitação negada e cooperado notificado.');
    }
}
