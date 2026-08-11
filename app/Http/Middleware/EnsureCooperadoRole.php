<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects users based on their role:
 * - Associado users trying to access admin routes → /cooperado
 * - Admin users trying to access cooperado routes → /dashboard
 */
class EnsureCooperadoRole
{
    public function handle(Request $request, Closure $next, string $guard = 'cooperado'): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/login');
        }

        if ($guard === 'cooperado') {
            // This route requires the Associado, Dependente, or Cliente role
            if (!$user->hasAnyRole(['Associado', 'Dependente', 'Cliente'])) {
                return redirect('/dashboard');
            }

            // Bloquear rotas exclusivas de cooperativa em tenants do tipo fundação
            if (function_exists('tenant') && tenant()?->tipo === 'fundacao') {
                $cooperativaOnlyPaths = ['cooperado/cotas', 'cooperado/financeiro'];
                foreach ($cooperativaOnlyPaths as $path) {
                    if ($request->is($path) || $request->is($path . '/*')) {
                        return redirect('/cooperado')->with('error', 'Esta funcionalidade é exclusiva para Cooperativas.');
                    }
                }
            }
        }

        if ($guard === 'admin') {
            // This route requires admin and should NOT be accessed by Associado, Dependente, or Cliente
            if ($user->hasAnyRole(['Associado', 'Cliente', 'Dependente']) && !$user->hasAnyRole(['Administrador Cooperativa', 'Administrador Fundação', 'Presidente', 'Gerente', 'Diretor Administrativo Financeiro', 'Consultor', 'Presidente do Conselho Fiscal', 'Auditoria', 'Contábil', 'Atendente', 'Conselho'])) {
                return redirect('/cooperado');
            }
        }

        return $next($request);
    }
}
