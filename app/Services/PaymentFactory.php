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
        // Check active payment setting
        $activeSetting = PaymentSetting::where('is_active', true)->first();

        if (!$activeSetting) {
            return new InternalBillingGateway();
        }

        switch ($activeSetting->provider) {
            case 'asaas':
                return new AsaasGateway($activeSetting->credentials ?? []);
            // Add other gateways like 'stripe', 'mercadopago', 'pix' here in the future
            case 'interno':
            default:
                return new InternalBillingGateway();
        }
    }
}
