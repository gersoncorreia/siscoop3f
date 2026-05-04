<?php

namespace App\Observers;

use App\Models\LancamentoFinanceiro;
use App\Models\CotaCapital;

class LancamentoFinanceiroObserver
{
    /**
     * Handle the LancamentoFinanceiro "updated" event.
     */
    public function updated(LancamentoFinanceiro $lancamento): void
    {
        // When a 'cota_capital' category payment is updated to 'pago' (from any other status)
        if ($lancamento->categoria === 'cota_capital' && 
            $lancamento->status === 'pago' && 
            $lancamento->getOriginal('status') !== 'pago') {
            
            // Add to the Associate's Cota Capital balance
            $cota = CotaCapital::where('associado_id', $lancamento->associado_id)->first();
            if ($cota) {
                $cota->saldo += $lancamento->valor;
                $cota->save();
            }
        }
        
        // Handle deduction if it changes from 'pago' to something else (e.g. chargeback/error)
        if ($lancamento->categoria === 'cota_capital' && 
            $lancamento->getOriginal('status') === 'pago' && 
            $lancamento->status !== 'pago') {
            
            $cota = CotaCapital::where('associado_id', $lancamento->associado_id)->first();
            if ($cota) {
                $cota->saldo -= $lancamento->valor;
                $cota->save();
            }
        }
    }

    // Other events...
}
