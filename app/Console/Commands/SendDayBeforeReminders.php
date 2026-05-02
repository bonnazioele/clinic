<?php

namespace App\Console\Commands;

use App\Jobs\SendDayBeforeReminderSms;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDayBeforeReminders extends Command
{
    protected $signature   = 'reminders:day-before';
    protected $description = 'Send day-before SMS reminders for tomorrow\'s appointments';

    public function handle(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        Appointment::whereDate('appointment_date', $tomorrow)
            ->with('patient')
            ->each(fn($appointment) => SendDayBeforeReminderSms::dispatch($appointment));

        $this->info('Day-before reminders dispatched.');
    }
}