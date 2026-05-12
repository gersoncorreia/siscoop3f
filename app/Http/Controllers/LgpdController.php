<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class LgpdController extends Controller
{
    /**
     * Exibir a página de aceite dos termos
     */
    public function show()
    {
        $submitRoute = tenant() ? 'lgpd.accept' : 'master-admin.lgpd.accept';
        return Inertia::render('Auth/Lgpd', [
            'submitRoute' => $submitRoute
        ]);
    }

    /**
     * Registrar aceite da LGPD
     */
    public function accept(Request $request)
    {
        $user = $request->user();

        \Illuminate\Support\Facades\Log::info('LGPD Accept clicked', [
            'user_id' => $user->id ?? null,
            'before' => $user->accepted_lgpd_at ?? null,
        ]);

        if ($user) {
            $user->update(['accepted_lgpd_at' => now()]);
            // Refresh to ensure it was saved
            $user->refresh();
            
            \Illuminate\Support\Facades\Log::info('LGPD updated', [
                'after' => $user->accepted_lgpd_at ?? null,
            ]);
        }

        $url = tenant() ? route('dashboard') : route('master-admin.dashboard');
        
        \Illuminate\Support\Facades\Log::info('LGPD Redirecting to', ['url' => $url]);
        
        return Inertia::location($url);
    }
}
