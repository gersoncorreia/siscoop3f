<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentSetting;
use App\Services\Gateways\InternalBillingGateway;
use App\Services\Gateways\AsaasGateway;

class PaymentFactory
{
    /**
     * Resolve the active payment gateway for the current tenant.
     */
    public static function resolve(): PaymentGatewayInterface
    {
        $tenant = tenant();
        $config = $tenant ? $tenant->payment_config : null;

        if (!$config || empty($config['active_gateway']) || $config['active_gateway'] === 'interno') {
            return new InternalBillingGateway();
        }

        switch ($config['active_gateway']) {
            case 'asaas':
                return new AsaasGateway($config['asaas'] ?? []);
            // Add other gateways like 'stripe', 'mercadopago', 'pix' here in the future
            default:
                return new InternalBillingGateway();
        }
    }
}
