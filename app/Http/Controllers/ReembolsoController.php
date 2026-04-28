<?php

namespace App\Http\Controllers;

use App\Models\Reembolso;
use App\Models\Associado;
use App\Http\Requests\Saude\StoreReembolsoRequest;
use App\Http\Requests\Saude\UpdateReembolsoRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReembolsoController extends Controller
{
    public function index()
    {
        return Inertia::render('Saude/Reembolsos/Index', [
            'reembolsos' => Reembolso::with('associado')->latest('created_at')->paginate(10),
            'associados' => Associado::select('id', 'nome')->get()
        ]);
    }

    public function store(StoreReembolsoRequest $request)
    {
        Reembolso::create($request->validated());

        return redirect()->back()->with('success', 'Solicitação de reembolso enviada!');
    }

    public function update(UpdateReembolsoRequest $request, Reembolso $reembolso)
    {
        $reembolso->update($request->validated());

        return redirect()->back()->with('success', 'Status do reembolso atualizado!');
    }

    public function destroy(Reembolso $reembolso)
    {
        $reembolso->delete();

        return redirect()->back()->with('success', 'Solicitação removida!');
    }

    public function aprovar(Reembolso $reembolso)
    {
        $reembolso->update([
            'status' => 'aprovado',
            'motivo_recusa' => null,
        ]);

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

    public function rejeitar(Request $request, Reembolso $reembolso)
    {
        $request->validate([
            'motivo_recusa' => 'required|string|max:1000',
        ]);

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
