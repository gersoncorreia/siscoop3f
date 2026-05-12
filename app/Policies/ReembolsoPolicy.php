<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reembolso;
use Illuminate\Auth\Access\Response;

class ReembolsoPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reembolso $reembolso): bool
    {
        if ($user->can('financeiro.view')) {
            return true;
        }

        return $user->associado && $user->associado->id === $reembolso->associado_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reembolso $reembolso): bool
    {
        return $user->can('financeiro.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reembolso $reembolso): bool
    {
        if ($user->can('financeiro.delete')) {
            return true;
        }

        // Cooperado só deleta se for o dono e se estiver rejeitado
        if ($user->associado && $user->associado->id === $reembolso->associado_id) {
            return $reembolso->status === 'rejeitado';
        }

        return false;
    }
}
