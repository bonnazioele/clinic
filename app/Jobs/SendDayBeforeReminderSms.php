<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\TwilioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SendDayBeforeReminderSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function handle(TwilioService $sms): void
    {
        $patient = $this->appointment->patient;
        $date    = Carbon::parse($this->appointment->appointment_date)->format('F d, Y');
        $time    = Carbon::parse($this->appointment->appointment_time)->format('h:i A');

        $message = "Hello {$patient->name}, this is a reminder that you have a clinic appointment tomorrow, {$date} at {$time}. Please be on time.";

        $sms->send($patient->phone, $message);
    }
}