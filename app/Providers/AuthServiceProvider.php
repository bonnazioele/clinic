<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
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
        $this->registerPolicies();

        Gate::define('access-admin-panel', function ($user) {
            return $user->is_system_admin; // boolean check from users table
        });

        Gate::define('access-secretary-panel', function ($user) {
            return $user->is_secretary; // boolean check from users table
        });
    }
}
