<?php

namespace App\Services\Repositories;

use App\Contracts\ReembolsoRepositoryInterface;
use App\Models\Associado;
use App\Models\Reembolso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReembolsoRepository implements ReembolsoRepositoryInterface
{
    public function all(): Collection
    {
        return Reembolso::all();
    }

    public function find(int|string $id): ?Model
    {
        return Reembolso::find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return Reembolso::findOrFail($id);
    }

    public function create(array $attributes): Model
    {
        return Reembolso::create($attributes);
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

    public function getPaginatedForAssociado(Associado $associado, int $perPage = 15): LengthAwarePaginator
    {
        return $associado->reembolsos()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
