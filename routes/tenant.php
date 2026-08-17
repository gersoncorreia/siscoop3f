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
    Route::get('/', [\App\Http\Controllers\Public\LandingPageController::class, 'index'])->name('landing');

        // Rotas Públicas de Adesão
        Route::get('/adesao', [\App\Http\Controllers\Public\AdesaoController::class , 'create'])->name('adesao.create');
        Route::post('/adesao', [\App\Http\Controllers\Public\AdesaoController::class , 'store'])->middleware('throttle:5,1')->name('adesao.store');
        Route::get('/adesao/sucesso', [\App\Http\Controllers\Public\AdesaoController::class , 'success'])->name('adesao.success');

        Route::middleware('auth')->group(function () {
            Route::get('/lgpd', [\App\Http\Controllers\Public\LgpdController::class , 'show'])->name('lgpd.show');
            Route::post('/lgpd', [\App\Http\Controllers\Public\LgpdController::class , 'accept'])->name('lgpd.accept');

            Route::get('/atualizar-acesso', [\App\Http\Controllers\Auth\PasswordController::class, 'showTemporaryPasswordForm'])->name('password.temporary.view');
            Route::post('/atualizar-acesso', [\App\Http\Controllers\Auth\PasswordController::class, 'updateTemporaryPassword'])->name('password.update.temporary');
        }
        );

        Route::middleware(['auth', \App\Http\Middleware\LgpdMiddleware::class, 'force.password'])->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class , 'index'])->name('dashboard');

            Route::get('/profile', [\App\Http\Controllers\ProfileController::class , 'edit'])->name('profile.edit');
            Route::patch('/profile', [\App\Http\Controllers\ProfileController::class , 'update'])->name('profile.update');
            Route::delete('/profile', [\App\Http\Controllers\ProfileController::class , 'destroy'])->name('profile.destroy');

            // Notificações
            Route::post('/notificacoes/{id}/ler', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
            Route::post('/notificacoes/ler-todas', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');


            // =====================================================
            // PORTAL DO CLIENTE (Fundação) / COOPERADO (Cooperativa)
            // =====================================================
            foreach (['cliente', 'cooperado'] as $prefix) {
                Route::middleware([\App\Http\Middleware\EnsureCooperadoRole::class . ':cooperado'])
                    ->prefix($prefix)
                    ->name($prefix . '.')
                    ->group(function () {
                        Route::get('/', [\App\Http\Controllers\Cooperado\CooperadoDashboardController::class, 'index'])->name('dashboard');
                        Route::get('/cotas', [\App\Http\Controllers\Cooperado\CooperadoFinanceiroController::class, 'cotaCapital'])->name('cotas');
                        
                        Route::get('/financeiro', [\App\Http\Controllers\Cooperado\CooperadoFinanceiroController::class, 'index'])->name('financeiro');
                        
                        // Rotas que exigem cadastro completo para solicitação de serviços específicos
                        Route::middleware('cadastro.completo')->group(function () {
                            Route::get('/reembolsos', [\App\Http\Controllers\Cooperado\CooperadoReembolsoController::class, 'index'])->name('reembolsos');
                            Route::post('/reembolsos', [\App\Http\Controllers\Cooperado\CooperadoReembolsoController::class, 'store'])->name('reembolsos.store');
                            Route::get('/reembolsos/{id}/download', [\App\Http\Controllers\Cooperado\CooperadoReembolsoController::class, 'download'])->name('reembolsos.download');
                            Route::delete('/reembolsos/{id}', [\App\Http\Controllers\Cooperado\CooperadoReembolsoController::class, 'destroy'])->name('reembolsos.destroy');
                        });
                        Route::get('/dependentes', [\App\Http\Controllers\Cooperado\CooperadoDependenteController::class, 'index'])->name('dependentes');
                        Route::post('/dependentes', [\App\Http\Controllers\Cooperado\CooperadoDependenteController::class, 'store'])->name('dependentes.store');
                        Route::put('/dependentes/{id}', [\App\Http\Controllers\Cooperado\CooperadoDependenteController::class, 'update'])->name('dependentes.update');
                        Route::delete('/dependentes/{id}', [\App\Http\Controllers\Cooperado\CooperadoDependenteController::class, 'destroy'])->name('dependentes.destroy');
                        Route::get('/documentos', [\App\Http\Controllers\Cooperado\CooperadoDocumentoController::class, 'index'])->name('documentos');
                        Route::post('/documentos', [\App\Http\Controllers\Cooperado\CooperadoDocumentoController::class, 'store'])->name('documentos.store');
                        Route::delete('/documentos/{id}', [\App\Http\Controllers\Cooperado\CooperadoDocumentoController::class, 'destroy'])->name('documentos.destroy');
                        Route::get('/documentos/{id}/download', [\App\Http\Controllers\Cooperado\CooperadoDocumentoController::class, 'download'])->name('documentos.download');

                        // Rede de Prestadores
                        Route::get('/prestadores', [\App\Http\Controllers\Cooperado\CooperadoServicoController::class, 'prestadores'])->name('prestadores');
                        Route::get('/prestadores/{id}', [\App\Http\Controllers\Cooperado\CooperadoServicoController::class, 'prestadorDetalhe'])->name('prestadores.show');
                        Route::get('/minhas-solicitacoes', [\App\Http\Controllers\Cooperado\CooperadoServicoController::class, 'minhasSolicitacoes'])->name('minhas-solicitacoes');

                        // Perfil do Cliente / Cooperado
                        Route::get('/perfil', [\App\Http\Controllers\Cooperado\CooperadoServicoController::class, 'perfil'])->name('perfil');
                        Route::patch('/perfil', [\App\Http\Controllers\Cooperado\CooperadoServicoController::class, 'updatePerfil'])->name('perfil.update');

                        // ---- Clube de Saúde / Fundação ----
                        // Agendamentos (Consultas e Exames)
                        Route::get('/agendamentos', [\App\Http\Controllers\Cooperado\AgendamentoController::class, 'index'])->name('agendamentos.index');
                        Route::get('/agendamentos/novo', [\App\Http\Controllers\Cooperado\AgendamentoController::class, 'create'])->name('agendamentos.create');
                        Route::post('/agendamentos', [\App\Http\Controllers\Cooperado\AgendamentoController::class, 'store'])->name('agendamentos.store');
                        Route::delete('/agendamentos/{agendamento}', [\App\Http\Controllers\Cooperado\AgendamentoController::class, 'destroy'])->name('agendamentos.destroy');

                        // Internações Eletivas
                        Route::get('/internacoes', [\App\Http\Controllers\Cooperado\InternacaoController::class, 'index'])->name('internacoes.index');
                        Route::post('/internacoes', [\App\Http\Controllers\Cooperado\InternacaoController::class, 'store'])->name('internacoes.store');

                        // Prontuário Eletrônico
                        Route::get('/prontuario', [\App\Http\Controllers\Cooperado\ProntuarioController::class, 'index'])->name('prontuario.index');

                        // Carteirinha Digital (Fundação)
                        Route::get('/carteirinha', [\App\Http\Controllers\Cooperado\CarteirinhaController::class, 'index'])->name('carteirinha');
                        Route::post('/carteirinha/solicitar', [\App\Http\Controllers\Cooperado\CarteirinhaController::class, 'solicitar'])->name('carteirinha.solicitar');

                        // Tabela de Serviços (Fundação - leitura)
                        Route::get('/servicos', [\App\Http\Controllers\Cooperado\TabelaServicoController::class, 'index'])->name('servicos');
                    });
            }

            // =====================================================
            // PORTAL DO PRESTADOR / PARCEIRO (Role: Prestador)
            // =====================================================
            Route::middleware(['role:Prestador'])
                ->prefix('prestador')
                ->name('prestador.')
                ->group(function () {
                    Route::get('/', [\App\Http\Controllers\PrestadorPortalController::class, 'dashboard'])->name('dashboard');
                    Route::post('/solicitar', [\App\Http\Controllers\PrestadorPortalController::class, 'solicitarAutorizacao'])->name('solicitar');
                    Route::get('/buscar-cooperado', [\App\Http\Controllers\PrestadorPortalController::class, 'buscarCooperado'])->name('buscar-cooperado');
                    
                    Route::get('/servicos', [\App\Http\Controllers\PrestadorPortalController::class, 'servicos'])->name('servicos');
                    Route::post('/servicos', [\App\Http\Controllers\PrestadorPortalController::class, 'storeServico'])->name('servicos.store');
                    Route::put('/servicos/{id}', [\App\Http\Controllers\PrestadorPortalController::class, 'updateServico'])->name('servicos.update');
                    Route::delete('/servicos/{id}', [\App\Http\Controllers\PrestadorPortalController::class, 'deleteServico'])->name('servicos.destroy');
                    
                    Route::get('/faturas', [\App\Http\Controllers\PrestadorPortalController::class, 'faturas'])->name('faturas');
                    
                    Route::get('/perfil', [\App\Http\Controllers\PrestadorPortalController::class, 'perfil'])->name('perfil');
                    Route::put('/perfil', [\App\Http\Controllers\PrestadorPortalController::class, 'updatePerfil'])->name('perfil.update');
                    Route::put('/perfil/senha', [\App\Http\Controllers\PrestadorPortalController::class, 'updateSenha'])->name('perfil.senha');
                });

            // =====================================================
             // PAINEL ADMINISTRATIVO (Role: Administrador Cooperativa)
            // =====================================================

            // Módulo de Associados
            Route::middleware(['role:Administrador Cooperativa|Presidente|Gerente|Auditoria|Contábil|Administrador Fundação|Atendente|Conselho'])->group(function () {
                Route::prefix('associados')->name('associados.')->group(function () {
                    Route::get('adesoes', [\App\Http\Controllers\Public\AdesaoController::class , 'index'])->name('adesoes.index');
                    Route::post('adesoes/{associado}/aprovar', [\App\Http\Controllers\Public\AdesaoController::class , 'aprovar'])->name('adesoes.aprovar');
                    Route::post('adesoes/{associado}/rejeitar', [\App\Http\Controllers\Public\AdesaoController::class , 'rejeitar'])->name('adesoes.rejeitar');
                    Route::resource('dependentes', \App\Http\Controllers\Admin\DependenteController::class);
                    Route::resource('convenios', \App\Http\Controllers\Admin\ConvenioController::class);

                    // Documentos
                    Route::post('{associado}/documentos', [\App\Http\Controllers\Admin\DocumentoController::class , 'store'])->name('documentos.store');
                    Route::delete('{associado}/documentos/{documento}', [\App\Http\Controllers\Admin\DocumentoController::class , 'destroy'])->name('documentos.destroy');
                    Route::get('{associado}/documentos/{documento}/download', [\App\Http\Controllers\Admin\DocumentoController::class , 'download'])->name('documentos.download');
                    Route::post('{associado}/documentos/{documento}/aprovar', [\App\Http\Controllers\Admin\DocumentoController::class , 'aprovar'])->name('documentos.aprovar');
                    Route::post('{associado}/documentos/{documento}/rejeitar', [\App\Http\Controllers\Admin\DocumentoController::class , 'rejeitar'])->name('documentos.rejeitar');
                }
                );
                Route::resource('associados', \App\Http\Controllers\Admin\AssociadoController::class);

                // Módulo Financeiro
                Route::prefix('financeiro')->name('financeiro.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\FinanceiroController::class , 'index'])->name('index');
                    Route::get('conciliacao', [\App\Http\Controllers\Admin\FinanceiroController::class , 'conciliacao'])->name('conciliacao');
                    Route::post('conciliacao/{lancamento}', [\App\Http\Controllers\Admin\FinanceiroController::class , 'conciliar'])->name('conciliar');
                    Route::resource('contas', \App\Http\Controllers\Admin\ContaBancariaController::class);
                    Route::resource('lancamentos', \App\Http\Controllers\Admin\LancamentoFinanceiroController::class);
                    Route::resource('capital', \App\Http\Controllers\Admin\CotaCapitalController::class);
                }
                );

                // Módulo de Saúde
                Route::prefix('saude')->name('saude.')->group(function () {
                    Route::resource('planos', \App\Http\Controllers\Admin\PlanoSaudeController::class);
                    Route::resource('prestadores', \App\Http\Controllers\Admin\PrestadorController::class);
                    
                    // Autorizações do Fluxo Invertido (Gerência/Hélio)
                    Route::get('autorizacoes', [\App\Http\Controllers\Admin\SaudeController::class, 'autorizacoesPendentes'])->name('autorizacoes.index');
                    Route::post('autorizacoes/{solicitacao}/aprovar', [\App\Http\Controllers\Admin\SaudeController::class, 'aprovarAutorizacao'])->name('autorizacoes.aprovar');
                    Route::post('autorizacoes/{solicitacao}/rejeitar', [\App\Http\Controllers\Admin\SaudeController::class, 'rejeitarAutorizacao'])->name('autorizacoes.rejeitar');
                    
                    // Faturamento (Regra do dia 05)
                    Route::get('faturas', [\App\Http\Controllers\Admin\SaudeController::class, 'faturas'])->name('faturas.index');
                    Route::post('faturas/{fatura}/auditar', [\App\Http\Controllers\Admin\SaudeController::class, 'auditarFatura'])->name('faturas.auditar');
                    Route::post('faturas/{fatura}/pagar', [\App\Http\Controllers\Admin\SaudeController::class, 'pagarFatura'])->name('faturas.pagar');

                    Route::resource('reembolsos', \App\Http\Controllers\Admin\ReembolsoController::class);
                    Route::post('reembolsos/{reembolso}/aprovar', [\App\Http\Controllers\Admin\ReembolsoController::class, 'aprovar'])->name('reembolsos.aprovar');
                    Route::post('reembolsos/{reembolso}/rejeitar', [\App\Http\Controllers\Admin\ReembolsoController::class, 'rejeitar'])->name('reembolsos.rejeitar');

                    // Agendamentos (Clube de Saúde / Fundação)
                    Route::prefix('agendamentos')->name('agendamentos.')->group(function () {
                        Route::get('/', [\App\Http\Controllers\Admin\AgendamentoAdminController::class, 'index'])->name('index');
                        Route::post('{agendamento}/confirmar', [\App\Http\Controllers\Admin\AgendamentoAdminController::class, 'confirmar'])->name('confirmar');
                        Route::post('{agendamento}/cancelar', [\App\Http\Controllers\Admin\AgendamentoAdminController::class, 'cancelar'])->name('cancelar');
                        Route::post('{agendamento}/realizar', [\App\Http\Controllers\Admin\AgendamentoAdminController::class, 'realizar'])->name('realizar');
                    });

                    // Internações Eletivas (Clube de Saúde / Fundação)
                    Route::prefix('internacoes')->name('internacoes.')->group(function () {
                        Route::get('/', [\App\Http\Controllers\Admin\InternacaoAdminController::class, 'index'])->name('index');
                        Route::post('{internacao}/aprovar', [\App\Http\Controllers\Admin\InternacaoAdminController::class, 'aprovar'])->name('aprovar');
                        Route::post('{internacao}/rejeitar', [\App\Http\Controllers\Admin\InternacaoAdminController::class, 'rejeitar'])->name('rejeitar');
                    });

                    // Campanhas de Saúde (Fundação)
                    Route::prefix('campanhas')->name('campanhas.')->group(function () {
                        Route::get('/', [\App\Http\Controllers\Admin\CampanhaSaudeController::class, 'index'])->name('index');
                        Route::post('/', [\App\Http\Controllers\Admin\CampanhaSaudeController::class, 'store'])->name('store');
                        Route::put('/{campanha}', [\App\Http\Controllers\Admin\CampanhaSaudeController::class, 'update'])->name('update');
                        Route::delete('/{campanha}', [\App\Http\Controllers\Admin\CampanhaSaudeController::class, 'destroy'])->name('destroy');
                        Route::post('/{campanha}/toggle', [\App\Http\Controllers\Admin\CampanhaSaudeController::class, 'toggleAtivo'])->name('toggle');
                    });

                    // Indicadores de Desempenho (Fundação)
                    Route::get('indicadores', [\App\Http\Controllers\Admin\IndicadoresController::class, 'index'])->name('indicadores');
                });

                // Tabela de Serviços (Fundação)
                Route::prefix('saude/tabela-servicos')->name('saude.tabela-servicos.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\TabelaServicoController::class, 'index'])->name('index');
                    Route::post('/', [\App\Http\Controllers\Admin\TabelaServicoController::class, 'store'])->name('store');
                    Route::put('/{tabelaServico}', [\App\Http\Controllers\Admin\TabelaServicoController::class, 'update'])->name('update');
                    Route::delete('/{tabelaServico}', [\App\Http\Controllers\Admin\TabelaServicoController::class, 'destroy'])->name('destroy');
                    Route::post('/{tabelaServico}/toggle', [\App\Http\Controllers\Admin\TabelaServicoController::class, 'toggleAtivo'])->name('toggle');
                });

                // Planos de Adesão (Fundação)
                Route::prefix('saude/planos-adesao')->name('saude.planos-adesao.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\PlanoAdesaoController::class, 'index'])->name('index');
                    Route::post('/', [\App\Http\Controllers\Admin\PlanoAdesaoController::class, 'store'])->name('store');
                    Route::put('/{planoAdesao}', [\App\Http\Controllers\Admin\PlanoAdesaoController::class, 'update'])->name('update');
                    Route::delete('/{planoAdesao}', [\App\Http\Controllers\Admin\PlanoAdesaoController::class, 'destroy'])->name('destroy');
                    Route::get('/{planoAdesao}/precos', [\App\Http\Controllers\Admin\PlanoAdesaoController::class, 'precos'])->name('precos');
                    Route::put('/{planoAdesao}/precos', [\App\Http\Controllers\Admin\PlanoAdesaoController::class, 'updatePrecos'])->name('precos.update');
                });

                // Módulo de Relatórios
                Route::middleware('permission:relatorios.view')->prefix('relatorios')->name('relatorios.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\RelatorioController::class, 'index'])->name('index');
                    Route::get('/export/associados', [\App\Http\Controllers\Admin\RelatorioController::class, 'exportAssociados'])->name('export.associados');
                    Route::get('/export/financeiro', [\App\Http\Controllers\Admin\RelatorioController::class, 'exportFinanceiro'])->name('export.financeiro');
                    Route::get('/export/dependentes', [\App\Http\Controllers\Admin\RelatorioController::class, 'exportDependentes'])->name('export.dependentes');
                    Route::get('/export/adesoes', [\App\Http\Controllers\Admin\RelatorioController::class, 'exportAdesoes'])->name('export.adesoes');
                    Route::get('/export/agendamentos', [\App\Http\Controllers\Admin\RelatorioController::class, 'exportAgendamentos'])->name('export.agendamentos');
                    Route::get('/export/prestadores', [\App\Http\Controllers\Admin\RelatorioController::class, 'exportPrestadores'])->name('export.prestadores');
                });

                // Configurações do Tenant
                Route::get('configuracoes', [\App\Http\Controllers\Admin\ConfiguracaoController::class , 'index'])->name('configuracoes.index');
                Route::post('configuracoes', [\App\Http\Controllers\Admin\ConfiguracaoController::class , 'update'])->name('configuracoes.update');
                Route::get('configuracoes/pagamentos', [\App\Http\Controllers\Admin\ConfiguracaoController::class, 'pagamentos'])->name('configuracoes.pagamentos');
                Route::post('configuracoes/pagamentos', [\App\Http\Controllers\Admin\ConfiguracaoController::class, 'updatePagamentos'])->name('configuracoes.pagamentos.update');
                Route::get('configuracoes/lgpd', [\App\Http\Controllers\Admin\ConfiguracaoController::class, 'lgpd'])->name('configuracoes.lgpd');
                Route::post('configuracoes/lgpd', [\App\Http\Controllers\Admin\ConfiguracaoController::class, 'updateLgpd'])->name('configuracoes.lgpd.update');

                // Contratos entre Entidades (Tenant solicita / Admin Master aprova)
                Route::get('configuracoes/contratos', [\App\Http\Controllers\Admin\ContratoTenantController::class, 'index'])->name('configuracoes.contratos');
                Route::post('configuracoes/contratos/solicitar', [\App\Http\Controllers\Admin\ContratoTenantController::class, 'solicitar'])->name('configuracoes.contratos.solicitar');
                Route::put('configuracoes/contratos/{id}/cancelar', [\App\Http\Controllers\Admin\ContratoTenantController::class, 'cancelar'])->name('configuracoes.contratos.cancelar');
                Route::get('configuracoes/contratos/{id}/download/{index}', [\App\Http\Controllers\Admin\ContratoTenantController::class, 'downloadArquivo'])->name('configuracoes.contratos.download-arquivo');
                
                // Configurações da Landing Page (OnePage)
                Route::get('configuracoes/landing-page', [\App\Http\Controllers\Admin\LandingPageSettingsController::class, 'edit'])->name('configuracoes.landing-page');
                Route::post('configuracoes/landing-page', [\App\Http\Controllers\Admin\LandingPageSettingsController::class, 'update'])->name('configuracoes.landing-page.update');

                // Gestão de Usuários Administrativos
                Route::prefix('configuracoes')->name('configuracoes.')->group(function () {
                    Route::resource('usuarios', \App\Http\Controllers\Admin\TenantUserController::class)->names([
                        'index' => 'usuarios.index',
                        'create' => 'usuarios.create',
                        'store' => 'usuarios.store',
                        'edit' => 'usuarios.edit',
                        'update' => 'usuarios.update',
                        'destroy' => 'usuarios.destroy',
                    ]);
                });
            }); // End Administrative Panel Role Middleware

            }
            );

            require __DIR__ . '/auth.php';
        });

