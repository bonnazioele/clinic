<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Appointment;
use App\Observers\AppointmentObserver;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
{
    Appointment::observe(AppointmentObserver::class);
}
}
