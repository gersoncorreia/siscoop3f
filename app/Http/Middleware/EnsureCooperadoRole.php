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
            // This route requires the Associado role
            if (!$user->hasRole('Associado')) {
                return redirect('/dashboard');
            }
        }

        if ($guard === 'admin') {
            // This route requires admin and should NOT be accessed by Associado
            if ($user->hasRole('Associado') && !$user->hasRole('Administrador Cooperativa')) {
                return redirect('/cooperado');
            }
        }

        return $next($request);
    }
}
