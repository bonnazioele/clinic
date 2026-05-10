<?php

namespace App\Notifications;

use App\Models\Clinic;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Route;

class ClinicRegistrationSubmitted extends Notification
{
    use Queueable;

    public function __construct(public Clinic $clinic)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $name = $this->clinic->name ?: 'New clinic';

        return [
            'title' => 'New clinic registration',
            'message' => $name . ' submitted a clinic registration for review.',
            'clinic_id' => $this->clinic->id,
            'url' => $this->applicationUrl(),
            'icon' => 'bi-building',
            'role' => 'admin',
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    private function applicationUrl(): ?string
    {
        if (! Route::has('admin.clinics.application')) {
            return null;
        }

        return route('admin.clinics.application', $this->clinic);
    }
}
