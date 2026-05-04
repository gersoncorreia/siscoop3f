<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;

class AsaasGateway implements PaymentGatewayInterface
{
    protected array $credentials;

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    public function gerarCobranca(array $data): array
    {
        // TODO: Implement Asaas API Call here using $this->credentials['api_key']
        
        return [
            'status' => 'pending',
            'gateway_id' => 'asaas_mock_id',
            'url' => 'https://sandbox.asaas.com/c/mockurl',
        ];
    }
}
