<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracoes/Index', [
            'tenant' => tenant()
        ]);
    }

    public function update(Request $request)
    {
        $tenant = tenant();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'name' => $validated['name'],
            'primary_color' => $validated['primary_color'],
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $path;
        }

        $tenant->update($data);

        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso!');
    }
}
