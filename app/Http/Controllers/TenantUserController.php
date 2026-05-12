<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules;

class TenantUserController extends Controller
{
    public function index()
    {
        Gate::authorize('configuracoes.users');

        // Listar usuários que NÃO são associados (ou todos os administrativos)
        // Para este sistema, os associados têm a role 'Associado'. 
        // Vamos listar todos e mostrar suas roles.
        $users = User::with('roles')->latest()->paginate(10);

        return Inertia::render('Configuracoes/Usuarios/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        Gate::authorize('configuracoes.users');

        return Inertia::render('Configuracoes/Usuarios/Form', [
            'roles' => Role::whereNotIn('name', ['Associado', 'Prestador'])->get(),
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('configuracoes.users');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|exists:roles,name|not_in:Associado,Prestador',
            'permissions' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        }

        return Redirect::route('configuracoes.usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $usuario)
    {
        Gate::authorize('configuracoes.users');

        return Inertia::render('Configuracoes/Usuarios/Form', [
            'user_edit' => $usuario->load(['roles', 'permissions']),
            'roles' => Role::whereNotIn('name', ['Associado', 'Prestador'])->get(),
            'permissions' => Permission::all(),
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        Gate::authorize('configuracoes.users');

        $isRestricted = $usuario->hasRole(['Associado', 'Prestador']);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $usuario->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ];

        if (!$isRestricted) {
            $rules['role'] = 'required|exists:roles,name|not_in:Associado,Prestador';
            $rules['permissions'] = 'array';
        }

        $request->validate($rules);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        if (!$isRestricted) {
            $usuario->syncRoles([$request->role]);
            $usuario->syncPermissions($request->permissions ?? []);
        }

        return Redirect::route('configuracoes.usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $usuario)
    {
        Gate::authorize('configuracoes.users');

        // Evitar que o próprio usuário se delete
        if (auth()->id() === $usuario->id) {
            return Redirect::back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $usuario->delete();

        return Redirect::route('configuracoes.usuarios.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
