<?php

namespace App\Contracts;

use App\Models\Associado;

interface AssociadoRepositoryInterface extends RepositoryInterface
{
    public function findByUserId(int|string $userId): ?Associado;
    public function getActiveAssociadoForUser(\App\Models\User $user): Associado;
}
