<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\LancamentoFinanceiro;
use Illuminate\Support\Str;

class InternalBillingGateway implements PaymentGatewayInterface
{
    public function gerarCobranca(array $data): array
    {
        // Internally, we just generate a unique reference id and assume success for testing.
        $gatewayId = 'INT-' . strtoupper(Str::random(10));

        // Note: The actual persistence to LancamentoFinanceiro can either be done here or in the caller.
        // To keep the Gateway generic, we return the data and let the caller persist, OR we persist here.
        // Let's return the generated reference.

        return [
            'status' => 'success',
            'gateway_id' => $gatewayId,
            'url' => null, // No external checkout URL
        ];
    }
}
