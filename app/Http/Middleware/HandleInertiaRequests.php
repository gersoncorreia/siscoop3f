<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'roles' => function () use ($request) {
                    try {
                        return $request->user()?->getRoleNames() ?? [];
                    } catch (\Throwable) {
                        return [];
                    }
                },
                'permissions' => function () use ($request) {
                    try {
                        return $request->user()?->getAllPermissions()->pluck('name') ?? [];
                    } catch (\Throwable) {
                        return [];
                    }
                },
                'unreadNotifications' => function () use ($request) {
                    try {
                        return $request->user()?->unreadNotifications ?? [];
                    } catch (\Throwable) {
                        return [];
                    }
                },
            ],
            'isCooperado' => function () use ($request) {
                try {
                    return $request->user()?->hasRole('Associado') ?? false;
                } catch (\Throwable) {
                    return false;
                }
            },
            'associado' => function () use ($request) {
                try {
                    if (!$request->user() || !$request->user()->hasRole('Associado')) {
                        return null;
                    }
                    $associado = \App\Models\Associado::where('user_id', $request->user()->id)->first();
                    if (!$associado) return null;
                    return [
                        'id' => $associado->id,
                        'nome' => $associado->nome,
                        'status' => $associado->status,
                        'matricula' => str_pad((string)$associado->id, 4, '0', STR_PAD_LEFT),
                    ];
                } catch (\Throwable) {
                    return null;
                }
            },
            'isCentral' => in_array($request->getHost(), config('tenancy.central_domains')),
            'isTenant' => !in_array($request->getHost(), config('tenancy.central_domains')),
            'tenant' => function () {
                if (function_exists('tenant')) {
                    $t = tenant();
                    if (!$t) return null;
                    return [
                        'id' => $t->id,
                        'name' => $t->name ?? $t->id,
                        'primary_color' => $t->primary_color ?? '#2563eb',
                        'logo_url' => $t->logo_path ? asset('storage/' . $t->logo_path) : null,
                    ];
                }
                return null;
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
