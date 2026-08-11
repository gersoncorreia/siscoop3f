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
            
            // Somente para cooperados, admins continuam
            if ($user->hasAnyRole(['Associado', 'Cliente']) && $user->associado) {
                if (!$user->associado->isCadastroCompleto()) {
                    return redirect()->route('profile.edit')->with('error', 'Por favor, antes de solicitar serviços, complete seu cadastro com seu endereço e data de nascimento.');
                }
            }
        }

        return $next($request);
    }
}
