<?php

namespace App\Console\Commands;

use App\Jobs\SendPreAppointmentReminderSms;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPreAppointmentReminders extends Command
{
    protected $signature   = 'reminders:pre-appointment';
    protected $description = 'Send 30-min-before SMS reminders';

    public function handle(): void
    {
        $today       = Carbon::today()->toDateString();
        $targetTime  = Carbon::now()->addMinutes(30);
        $windowStart = $targetTime->copy()->second(0)->format('H:i:s');
        $windowEnd   = $targetTime->copy()->second(59)->format('H:i:s');

        Appointment::whereDate('appointment_date', $today)
            ->whereBetween('appointment_time', [$windowStart, $windowEnd])
            ->with('patient')
            ->each(fn($appointment) => SendPreAppointmentReminderSms::dispatch($appointment));

        $this->info('Pre-appointment reminders dispatched.');
    }
}