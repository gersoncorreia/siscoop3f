<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Associado;
use Illuminate\Auth\Access\Response;

class AssociadoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('associados.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Associado $associado): bool
    {
        // Administrador ou o próprio dono
        if ($user->can('associados.view')) {
            return true;
        }

        return $user->associado && $user->associado->id === $associado->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('associados.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Associado $associado): bool
    {
        if ($user->can('associados.update')) {
            return true;
        }

        return $user->associado && $user->associado->id === $associado->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Associado $associado): bool
    {
        return $user->can('associados.delete');
    }
}
