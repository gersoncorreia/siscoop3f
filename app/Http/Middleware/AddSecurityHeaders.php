<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent modification of already sent responses or non-objects
        if (method_exists($response, 'headers')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
            
            // Build Content-Security-Policy
            $csp = "default-src 'self'; "
                 . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.bunny.net; "
                 . "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; "
                 . "img-src 'self' data: blob:; "
                 . "font-src 'self' https://fonts.bunny.net; "
                 . "connect-src 'self' ws: wss:; "
                 . "frame-ancestors 'self';";
            
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}
