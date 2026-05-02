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

class SendPreAppointmentReminderSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function handle(TwilioService $sms): void
    {
        $patient = $this->appointment->patient;
        $time    = Carbon::parse($this->appointment->appointment_time)->format('h:i A');

        $message = "Hello {$patient->name}, your clinic appointment is in 30 minutes at {$time}. Please proceed to the clinic on time.";

        $sms->send($patient->phone, $message);
    }
}