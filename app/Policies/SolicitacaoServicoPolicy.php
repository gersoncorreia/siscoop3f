<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SolicitacaoServico;
use Illuminate\Auth\Access\Response;

class SolicitacaoServicoPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SolicitacaoServico $solicitacao): bool
    {
        // Admin
        if ($user->can('saude.view')) {
            return true;
        }

        // Prestador (donos da solicitação)
        if ($user->hasRole('Prestador')) {
            return $user->id === $solicitacao->prestador->user_id;
        }

        // Cooperado (beneficiário)
        return $user->associado && $user->associado->id === $solicitacao->associado_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SolicitacaoServico $solicitacao): bool
    {
        return $user->can('saude.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SolicitacaoServico $solicitacao): bool
    {
        return $user->can('saude.delete');
    }
}
