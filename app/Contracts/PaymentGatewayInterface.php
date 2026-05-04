<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Generate a charge for the given associative array of data.
     *
     * @param array $data Contains info like associado_id, total, category, description, etc.
     * @return array Returns an array with ['status' => 'success|error', 'gateway_id' => '...', 'url' => '...']
     */
    public function gerarCobranca(array $data): array;
}
