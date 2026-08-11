<?php

namespace App\Services\Repositories;

use App\Contracts\AssociadoRepositoryInterface;
use App\Models\Associado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class AssociadoRepository implements AssociadoRepositoryInterface
{
    public function all(): Collection
    {
        return Associado::all();
    }

    public function find(int|string $id): ?Model
    {
        return Associado::find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return Associado::findOrFail($id);
    }

    public function create(array $attributes): Model
    {
        return Associado::create($attributes);
    }

    public function update(int|string $id, array $attributes): bool
    {
        $model = $this->findOrFail($id);
        return $model->update($attributes);
    }

    public function delete(int|string $id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    public function findByUserId(int|string $userId): ?Associado
    {
        return Associado::where('user_id', $userId)->first();
    }

    public function getActiveAssociadoForUser(\App\Models\User $user): Associado
    {
        if ($user->associado) {
            return $user->associado;
        }

        if ($user->dependente && $user->dependente->associado) {
            return $user->dependente->associado;
        }

        abort(403, 'Nenhum cadastro de associado ou dependente vinculado a este usuário.');
    }
}
