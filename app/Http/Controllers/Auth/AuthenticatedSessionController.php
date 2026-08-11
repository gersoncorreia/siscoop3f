<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        $view = tenant() ? 'Auth/TenantLogin' : 'Auth/AdminLogin';

        $t = tenant();

        return Inertia::render($view, [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'tenant' => $t ? [
                'id' => $t->id,
                'name' => $t->name ?? $t->nome ?? $t->id,
                'primary_color' => $t->primary_color ?? '#2563eb',
                'logo_url' => $t->logo_path ? asset('storage/' . $t->logo_path) : null,
            ] : null,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $url = tenant() ? route('dashboard') : route('master-admin.dashboard');
        return redirect()->intended($url);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
