<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCadastroCompleto
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        if (auth()->check()) {
            $user = auth()->user();
            
            // Somente para cooperados/clientes, admins continuam
            if ($user->hasAnyRole(['Associado', 'Cliente']) && $user->associado) {
                // Permitir visualizar extrato financeiro sem bloqueio
                if ($request->is('*/financeiro') || $request->is('financeiro')) {
                    return $next($request);
                }

                if (!$user->associado->isCadastroCompleto()) {
                    $targetRoute = (function_exists('tenant') && tenant('tipo') === 'fundacao') ? 'cliente.perfil' : 'cooperado.perfil';
                    return redirect()->route($targetRoute)->with('error', 'Por favor, antes de solicitar serviços, complete seu cadastro com seu endereço e data de nascimento.');
                }
            }
        }

        return $next($request);
    }
}
