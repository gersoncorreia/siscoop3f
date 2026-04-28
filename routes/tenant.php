<?php

declare(strict_types = 1)
;

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/* |-------------------------------------------------------------------------- | Tenant Routes |-------------------------------------------------------------------------- | | Here you can register the tenant routes for your application. | These routes are loaded by the TenantRouteServiceProvider. | | Feel free to customize them however you want. Good luck! | */

Route::middleware([
    InitializeTenancyByDomain::class ,
    PreventAccessFromCentralDomains::class ,
    'web',
])->group(function () {
    Route::get('/', function () {
        if (auth()->check()) {
            return redirect('/dashboard');
        }
        return redirect('/login');
    });

        Route::get('/debug-session', function () {
            $count = session('count', 0);
            session(['count' => $count + 1]);
            return response()->json(['count' => $count, 'tenant' => tenant('id')]);
        });

        Route::get('/debug-login', function() {
            $user = \App\Models\User::where('email', 'admin@stfsaude.com.br')->first();
            \Illuminate\Support\Facades\Auth::login($user);
            return response()->json([
                'auth_check' => \Illuminate\Support\Facades\Auth::check(),
                'user' => \Illuminate\Support\Facades\Auth::user()->email,
                'session_id' => session()->getId(),
            ]);
        });
        
        Route::get('/debug-check', function() {
            return response()->json([
                'auth_check' => \Illuminate\Support\Facades\Auth::check(),
                'user' => \Illuminate\Support\Facades\Auth::user()?->email,
                'session_id' => session()->getId(),
            ]);
        });

        // Rotas Públicas de Adesão
        Route::get('/adesao', [\App\Http\Controllers\AdesaoController::class , 'create'])->name('adesao.create');
        Route::post('/adesao', [\App\Http\Controllers\AdesaoController::class , 'store'])->name('adesao.store');
        Route::get('/adesao/sucesso', [\App\Http\Controllers\AdesaoController::class , 'success'])->name('adesao.success');

        Route::middleware('auth')->group(function () {
            Route::get('/lgpd', [\App\Http\Controllers\LgpdController::class , 'show'])->name('lgpd.show');
            Route::post('/lgpd', [\App\Http\Controllers\LgpdController::class , 'accept'])->name('lgpd.accept');

            Route::get('/atualizar-acesso', [\App\Http\Controllers\Auth\PasswordController::class, 'showTemporaryPasswordForm'])->name('password.temporary.view');
            Route::post('/atualizar-acesso', [\App\Http\Controllers\Auth\PasswordController::class, 'updateTemporaryPassword'])->name('password.update.temporary');
        }
        );

        Route::middleware(['auth', \App\Http\Middleware\LgpdMiddleware::class, 'force.password'])->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class , 'index'])->name('dashboard');

            Route::get('/profile', [\App\Http\Controllers\ProfileController::class , 'edit'])->name('profile.edit');
            Route::patch('/profile', [\App\Http\Controllers\ProfileController::class , 'update'])->name('profile.update');
            Route::delete('/profile', [\App\Http\Controllers\ProfileController::class , 'destroy'])->name('profile.destroy');

            // Notificações
            Route::post('/notificacoes/{id}/ler', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
            Route::post('/notificacoes/ler-todas', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');


            // =====================================================
            // PORTAL DO COOPERADO (Role: Associado)
            // =====================================================
            Route::middleware([\App\Http\Middleware\EnsureCooperadoRole::class . ':cooperado'])
                ->prefix('cooperado')
                ->name('cooperado.')
                ->group(function () {
                    Route::get('/', [\App\Http\Controllers\CooperadoPortalController::class, 'dashboard'])->name('dashboard');
                    Route::get('/cotas', [\App\Http\Controllers\CooperadoPortalController::class, 'cotaCapital'])->name('cotas');
                    
                    // Rotas que exigem cadastro completo
                    Route::middleware('cadastro.completo')->group(function () {
                        Route::get('/financeiro', [\App\Http\Controllers\CooperadoPortalController::class, 'financeiro'])->name('financeiro');
                        Route::get('/reembolsos', [\App\Http\Controllers\CooperadoPortalController::class, 'reembolsos'])->name('reembolsos');
                        Route::post('/reembolsos', [\App\Http\Controllers\CooperadoPortalController::class, 'solicitarReembolso'])->name('reembolsos.store');
                        Route::get('/reembolsos/{id}/download', [\App\Http\Controllers\CooperadoPortalController::class, 'downloadComprovanteReembolso'])->name('reembolsos.download');
                        Route::delete('/reembolsos/{id}', [\App\Http\Controllers\CooperadoPortalController::class, 'deleteReembolso'])->name('reembolsos.destroy');
                    });
                    Route::get('/dependentes', [\App\Http\Controllers\CooperadoPortalController::class, 'dependentes'])->name('dependentes');
                    Route::post('/dependentes', [\App\Http\Controllers\CooperadoPortalController::class, 'storeDependente'])->name('dependentes.store');
                    Route::put('/dependentes/{id}', [\App\Http\Controllers\CooperadoPortalController::class, 'updateDependente'])->name('dependentes.update');
                    Route::delete('/dependentes/{id}', [\App\Http\Controllers\CooperadoPortalController::class, 'deleteDependente'])->name('dependentes.destroy');
                    Route::get('/documentos', [\App\Http\Controllers\CooperadoPortalController::class, 'documentos'])->name('documentos');
                    Route::post('/documentos', [\App\Http\Controllers\CooperadoPortalController::class, 'uploadDocumento'])->name('documentos.store');
                    Route::delete('/documentos/{id}', [\App\Http\Controllers\CooperadoPortalController::class, 'deleteDocumento'])->name('documentos.destroy');
                    Route::get('/documentos/{id}/download', [\App\Http\Controllers\CooperadoPortalController::class, 'downloadDocumento'])->name('documentos.download');

                    // Perfil do Cooperado
                    Route::get('/perfil', [\App\Http\Controllers\CooperadoPortalController::class, 'perfil'])->name('perfil');
                    Route::patch('/perfil', [\App\Http\Controllers\ProfileController::class, 'update'])->name('perfil.update');
                    Route::delete('/perfil', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('perfil.destroy');
                });

            // =====================================================
            // PAINEL ADMINISTRATIVO (Role: Administrador Cooperativa)
            // =====================================================

            // Módulo de Associados
            Route::prefix('associados')->name('associados.')->group(function () {
                    Route::get('adesoes', [\App\Http\Controllers\AdesaoController::class , 'index'])->name('adesoes.index');
                    Route::post('adesoes/{associado}/aprovar', [\App\Http\Controllers\AdesaoController::class , 'aprovar'])->name('adesoes.aprovar');
                    Route::post('adesoes/{associado}/rejeitar', [\App\Http\Controllers\AdesaoController::class , 'rejeitar'])->name('adesoes.rejeitar');
                    Route::resource('dependentes', \App\Http\Controllers\DependenteController::class);
                    Route::resource('convenios', \App\Http\Controllers\ConvenioController::class);

                    // Documentos
                    Route::post('{associado}/documentos', [\App\Http\Controllers\DocumentoController::class , 'store'])->name('documentos.store');
                    Route::delete('{associado}/documentos/{documento}', [\App\Http\Controllers\DocumentoController::class , 'destroy'])->name('documentos.destroy');
                    Route::get('{associado}/documentos/{documento}/download', [\App\Http\Controllers\DocumentoController::class , 'download'])->name('documentos.download');
                    Route::post('{associado}/documentos/{documento}/aprovar', [\App\Http\Controllers\DocumentoController::class , 'aprovar'])->name('documentos.aprovar');
                    Route::post('{associado}/documentos/{documento}/rejeitar', [\App\Http\Controllers\DocumentoController::class , 'rejeitar'])->name('documentos.rejeitar');
                }
                );
                Route::resource('associados', \App\Http\Controllers\AssociadoController::class);

                // Módulo Financeiro
                Route::prefix('financeiro')->name('financeiro.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\FinanceiroController::class , 'index'])->name('index');
                    Route::get('conciliacao', [\App\Http\Controllers\FinanceiroController::class , 'conciliacao'])->name('conciliacao');
                    Route::post('conciliacao/{lancamento}', [\App\Http\Controllers\FinanceiroController::class , 'conciliar'])->name('conciliar');
                    Route::resource('contas', \App\Http\Controllers\ContaBancariaController::class);
                    Route::resource('lancamentos', \App\Http\Controllers\LancamentoFinanceiroController::class);
                    Route::resource('capital', \App\Http\Controllers\CotaCapitalController::class);
                }
                );

                // Módulo de Saúde
                Route::prefix('saude')->name('saude.')->group(function () {
                    Route::resource('planos', \App\Http\Controllers\PlanoSaudeController::class);
                    Route::resource('prestadores', \App\Http\Controllers\PrestadorController::class);
                    Route::resource('reembolsos', \App\Http\Controllers\ReembolsoController::class);
                    Route::post('reembolsos/{reembolso}/aprovar', [\App\Http\Controllers\ReembolsoController::class, 'aprovar'])->name('reembolsos.aprovar');
                    Route::post('reembolsos/{reembolso}/rejeitar', [\App\Http\Controllers\ReembolsoController::class, 'rejeitar'])->name('reembolsos.rejeitar');
                }
                );

                // Configurações do Tenant
                Route::get('configuracoes', [\App\Http\Controllers\ConfiguracaoController::class , 'index'])->name('configuracoes.index');
                Route::post('configuracoes', [\App\Http\Controllers\ConfiguracaoController::class , 'update'])->name('configuracoes.update');

                // Gestão de Usuários Administrativos
                Route::prefix('configuracoes')->name('configuracoes.')->group(function () {
                    Route::resource('usuarios', \App\Http\Controllers\TenantUserController::class)->names([
                        'index' => 'usuarios.index',
                        'create' => 'usuarios.create',
                        'store' => 'usuarios.store',
                        'edit' => 'usuarios.edit',
                        'update' => 'usuarios.update',
                        'destroy' => 'usuarios.destroy',
                    ]);
                });
            }
            );

            require __DIR__ . '/auth.php';
        });

