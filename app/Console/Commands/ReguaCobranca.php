<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\LancamentoFinanceiro;

class ReguaCobranca extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:ruler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa a régua de cobrança para títulos vencidos em todos os tenants.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando Régua de Cobrança...');

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $tenant->run(function () use ($tenant) {
                $count = 0;
                $atrasados = LancamentoFinanceiro::with('associado')
                    ->where('status', 'pendente')
                    ->where('tipo', 'receita')
                    ->where('data_vencimento', '<', now()->startOfDay())
                    ->get();

                foreach ($atrasados as $lancamento) {
                    $lancamento->update(['status' => 'atrasado']);
                    $count++;
                // TODO: Disparar Notification/Email para o associado
                // Mail::to($lancamento->associado->email)->send(new AvisoAtraso($lancamento));
                }

                if ($count > 0) {
                    $this->info("Tenant {$tenant->id}: {$count} lançamentos marcados como atrasados.");
                }
            });
        }

        $this->info('Régua de cobrança concluída.');
    }
}
