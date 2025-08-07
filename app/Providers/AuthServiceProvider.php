<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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

        Gate::define('access-secretary-panel', function ($user, $clinicId = null) {
            // Check if user is system admin (has access to everything)
            if ($user->is_system_admin) {
                return true;
            }
            
            // If no specific clinic ID provided, check if user is secretary at any clinic
            if ($clinicId === null) {
                return $user->isSecretary(); // Check if secretary at any clinic
            }
            
            // Check if user has secretary role at specific clinic
            return $user->isSecretaryAt($clinicId);
        });

        // Define clinic-specific access control
        Gate::define('access-clinic-data', function ($user, $clinicId) {
            // Admins can access all clinic data
            if ($user->is_system_admin) {
                return true;
            }
            
            // Check if user has any role at the specified clinic
            return $user->clinicUserRoles()
                ->where('clinic_id', $clinicId)
                ->where('is_active', true)
                ->exists();
        });

        // Define secretary-specific clinic access
        Gate::define('manage-clinic-appointments', function ($user, $clinicId) {
            return $user->is_system_admin || $user->isSecretaryAt($clinicId);
        });

        // Define doctor management access for secretaries
        Gate::define('manage-clinic-doctors', function ($user, $clinicId) {
            return $user->is_system_admin || $user->isSecretaryAt($clinicId);
        });
    }
}
