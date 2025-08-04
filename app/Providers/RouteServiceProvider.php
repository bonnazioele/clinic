<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Ensure admin routes are protected by 'can:access-admin-panel' middleware
        Route::middleware(['web', 'auth', 'can:access-admin-panel'])
            ->prefix('admin')
            ->as('admin.')
            ->group(base_path('routes/admin.php'));

        // Ensure secretary routes are protected by 'can:access-secretary-panel' middleware
        Route::middleware(['web', 'auth', 'can:access-secretary-panel'])
            ->prefix('secretary')
            ->as('secretary.')
            ->group(base_path('routes/secretary.php'));
    }
}
