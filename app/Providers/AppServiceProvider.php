<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Contracts\AssociadoRepositoryInterface::class,
            \App\Services\Repositories\AssociadoRepository::class
        );
        $this->app->bind(
            \App\Contracts\ReembolsoRepositoryInterface::class,
            \App\Services\Repositories\ReembolsoRepository::class
        );
        $this->app->singleton(\App\Services\StorageService::class);
        $this->app->singleton(\App\Services\CooperativeService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Vite::prefetch(concurrency: 3);

        // Safe Monolog processor to prevent circular dependency during boot
        if ($this->app->has('log')) {
            $logger = $this->app->make('log');
            $logger->pushProcessor(new \App\Logging\ContextMonologProcessor());
        }

        \App\Models\LancamentoFinanceiro::observe(\App\Observers\LancamentoFinanceiroObserver::class);
    }
}
