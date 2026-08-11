<?php

namespace App\Contracts;

use App\Models\Associado;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReembolsoRepositoryInterface extends RepositoryInterface
{
    public function getPaginatedForAssociado(Associado $associado, int $perPage = 15): LengthAwarePaginator;
}
