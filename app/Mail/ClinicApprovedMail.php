<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClinicApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $clinicName;
    public string $name;
    public string $email;
    public ?string $password;
    public string $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $clinicName, string $name, string $email, ?string $password, string $loginUrl)
    {
        $this->clinicName = $clinicName;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password; // May be null if user already existed
        $this->loginUrl = $loginUrl;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Your Clinic Has Been Approved — Account Access')
            ->view('emails.clinic_approved')
            ->with([
                'clinicName' => $this->clinicName,
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'loginUrl' => $this->loginUrl,
            ]);
    }
}
