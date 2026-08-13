<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

foreach (config('tenancy.central_domains', []) as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return Inertia::render('Welcome', [
                'canLogin' => false, // Hide on central domain
                'canRegister' => false, // Hide on central domain
                'laravelVersion' => Application::VERSION,
                'phpVersion' => PHP_VERSION,
            ]);
        });
    });
}

Route::middleware('auth')->group(function () {
    Route::get('/master-admin/lgpd', [\App\Http\Controllers\Public\LgpdController::class , 'show'])->name('master-admin.lgpd.show');
    Route::post('/master-admin/lgpd', [\App\Http\Controllers\Public\LgpdController::class , 'accept'])->name('master-admin.lgpd.accept');
});

Route::middleware(['auth', \App\Http\Middleware\LgpdMiddleware::class, 'super_admin'])->group(function () {
    Route::resource('tenants', \App\Http\Controllers\TenantController::class)->only(['store', 'update', 'destroy']);
    Route::post('tenants/{tenant}/restore', [\App\Http\Controllers\TenantController::class, 'restore'])->name('tenants.restore');
    Route::delete('tenants/{tenant}/force-delete', [\App\Http\Controllers\TenantController::class, 'forceDelete'])->name('tenants.forceDelete');
    Route::get('tenants/{tenant}/export', [\App\Http\Controllers\TenantController::class, 'export'])->name('tenants.export');

    Route::get('/master-admin/contratos', [\App\Http\Controllers\ContratoCentralController::class, 'index'])->name('master-admin.contratos.index');
    Route::post('/master-admin/contratos', [\App\Http\Controllers\ContratoCentralController::class, 'store'])->name('master-admin.contratos.store');
    Route::put('/master-admin/contratos/{id}', [\App\Http\Controllers\ContratoCentralController::class, 'update'])->name('master-admin.contratos.update');
    Route::delete('/master-admin/contratos/{id}', [\App\Http\Controllers\ContratoCentralController::class, 'destroy'])->name('master-admin.contratos.destroy');

    Route::get('/master-admin/dashboard', function () {
        // Need to get soft deleted tenants as well to display them
        $tenants = \App\Models\Tenant::withTrashed()->with('domains')->get();
        return Inertia::render('Dashboard', [
            'tenants' => $tenants,
            'isCentral' => true,
            'superAdminKpis' => [
                'totalEntidades' => $tenants->count(),
                'novasEsteMes' => $tenants->filter(function ($t) {
                    return $t->created_at >= now()->startOfMonth();
                })->count(),
                'totalDominios' => \Illuminate\Support\Facades\DB::table('domains')->count(),
            ]
        ]);
    })->name('master-admin.dashboard');

            // Master Admin Profile
            Route::get('/master-admin/profile', [ProfileController::class, 'edit'])->name('master-admin.profile.edit');
            Route::patch('/master-admin/profile', [ProfileController::class, 'update'])->name('master-admin.profile.update');
            Route::delete('/master-admin/profile', [ProfileController::class, 'destroy'])->name('master-admin.profile.destroy');
        });

// Profile routes for central domain only (master admin)
// Tenant profile is handled by tenant.php routes

// Master Admin Auth Routes
Route::group(['prefix' => 'master-admin', 'as' => 'master-admin.'], function () {
    require __DIR__ . '/auth.php';
});

// Internal Dev Logs
Route::get('/internal/zynix-logs-{secret}', [\App\Http\Controllers\Admin\InternalLogController::class, 'index'])
    ->middleware(\App\Http\Middleware\DevAccessMiddleware::class)
    ->name('internal.logs');
