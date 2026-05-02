<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Appointment;
use App\Observers\AppointmentObserver;
use App\Services\SemaphoreService;



class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SemaphoreService::class);
    }

    public function boot(): void
{
    Appointment::observe(AppointmentObserver::class);
}
}
