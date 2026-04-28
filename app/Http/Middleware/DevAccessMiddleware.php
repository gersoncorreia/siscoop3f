<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DevAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('app.dev_secret');
        
        if (!$secret) {
            abort(404, 'System Log feature is not configured.');
        }

        // Check if the secret is in the URL or in the header/session
        if ($request->route('secret') !== $secret && $request->header('X-Dev-Secret') !== $secret) {
            
            // For local development, we can be more lenient if needed, 
            // but for safety, we require the secret.
            if (!app()->environment('local')) {
                abort(404);
            }
        }

        return $next($request);
    }
}
