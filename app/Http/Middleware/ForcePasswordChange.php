<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->has_temporary_password) {
            // Permitir acesso às rotas necessárias para mudar a senha e sair
            if (!$request->is('atualizar-acesso') && !$request->routeIs('password.update.temporary') && !$request->is('logout')) {
                return redirect()->route('password.temporary.view');
            }
        }

        return $next($request);
    }
}
