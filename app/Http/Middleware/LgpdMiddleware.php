<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LgpdMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && is_null($user->accepted_lgpd_at)) {
            // Evitar loop infinito na tela de LGPD
            if (!$request->routeIs('lgpd.*', 'master-admin.lgpd.*', 'logout')) {
                $routeName = tenant() ? 'lgpd.show' : 'master-admin.lgpd.show';
                return redirect()->route($routeName);
            }
        }

        return $next($request);
    }
}
