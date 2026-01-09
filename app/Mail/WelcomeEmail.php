<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $plainPassword)
    {
    }

    public function build()
    {
        return $this->from('cliniq@gmail.com', 'CliniQ')
            ->subject('Welcome to CliniQ')
            ->view('emails.welcome')
            ->with([
                'user' => $this->user,
                'password' => $this->plainPassword,
                'loginUrl' => route('login'),
            ]);
    }
}
