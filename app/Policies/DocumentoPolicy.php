<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Documento;
use Illuminate\Auth\Access\Response;

class DocumentoPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Documento $documento): bool
    {
        // Se for admin
        if ($user->can('associados.view')) {
            return true;
        }

        // Se for o dono do documento (via associado)
        if ($user->associado && $user->associado->id === $documento->associado_id) {
            return true;
        }

        // Se for o dono do documento (via dependente do associado)
        if ($documento->dependente_id && $user->associado) {
            return $user->associado->dependentes()->where('id', $documento->dependente_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Documento $documento): bool
    {
        return $user->can('associados.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Documento $documento): bool
    {
        // Admin
        if ($user->can('associados.delete')) {
            return true;
        }

        // O cooperado só pode deletar se for o dono e se o documento estiver 'rejeitado'
        if ($user->associado && $user->associado->id === $documento->associado_id) {
            return $documento->status === 'rejeitado';
        }

        return false;
    }
}
