<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reembolso;
use Illuminate\Auth\Access\Response;

class ReembolsoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('saude.view') || $user->can('financeiro.view') || $user->hasRole(['Administrador Fundação', 'Administrador Cooperativa', 'Presidente', 'Gerente']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reembolso $reembolso): bool
    {
        if ($user->can('saude.view') || $user->can('financeiro.view') || $user->hasRole(['Administrador Fundação', 'Administrador Cooperativa', 'Presidente', 'Gerente'])) {
            return true;
        }

        return $user->associado && $user->associado->id === $reembolso->associado_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reembolso $reembolso): bool
    {
        return $user->can('saude.update') || $user->can('financeiro.update') || $user->hasRole(['Administrador Fundação', 'Administrador Cooperativa', 'Presidente', 'Gerente']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reembolso $reembolso): bool
    {
        if ($user->can('saude.delete') || $user->can('financeiro.delete') || $user->hasRole(['Administrador Fundação', 'Administrador Cooperativa', 'Presidente', 'Gerente'])) {
            return true;
        }

        // Cooperado só deleta se for o dono e se estiver rejeitado
        if ($user->associado && $user->associado->id === $reembolso->associado_id) {
            return $reembolso->status === 'rejeitado';
        }

        return false;
    }
}
