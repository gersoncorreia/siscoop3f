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

        if ($user) {
            $user->update(['accepted_lgpd_at' => now()]);
        }

        $url = tenant() ? route('dashboard') : route('master-admin.dashboard');
        return Redirect::to($url);
    }
}
