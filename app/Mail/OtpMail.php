<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $username;
    public $otp;

    public function __construct($username, $otp)
    {
        $this->username = $username;
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Your OTP Code')
                    ->view('emails.otp')
                    ->with([
                        'username' => $this->username,
                        'otp' => $this->otp
                    ]);
    }
}
