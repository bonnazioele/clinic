<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClinicDeclinedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clinicName;
    public string $name;
    public string $email;
    public string $reason;
    public string $reapplyUrl;

    public function __construct(string $clinicName, string $name, string $email, string $reason, string $reapplyUrl)
    {
        $this->clinicName = $clinicName;
        $this->name = $name;
        $this->email = $email;
        $this->reason = $reason;
        $this->reapplyUrl = $reapplyUrl;
    }

    public function build(): self
    {
        return $this->subject('Update on Your Clinic Application')
            ->view('emails.clinic_declined')
            ->with([
                'clinicName' => $this->clinicName,
                'name' => $this->name,
                'email' => $this->email,
                'reason' => $this->reason,
                'reapplyUrl' => $this->reapplyUrl,
            ]);
    }
}