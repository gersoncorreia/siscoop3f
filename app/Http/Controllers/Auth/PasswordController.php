<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
    /**
     * Show the view to force password change.
     */
    public function showTemporaryPasswordForm()
    {
        return \Inertia\Inertia::render('Auth/ForcePasswordUpdate');
    }

    /**
     * Force update the temporary password.
     */
    public function updateTemporaryPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'has_temporary_password' => false,
        ]);

        return redirect()->route('dashboard')->with('success', 'Acesso definitivo configurado com sucesso! Bem-vindo.');
    }
}
