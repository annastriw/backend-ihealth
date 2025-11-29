<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $title;
    public $name;

    public function __construct(string $otp, string $title = 'Kode OTP', string $name = null)
    {
        $this->otp = $otp;
        $this->title = $title;
        $this->name = $name;
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.otp')
                    ->with([
                        'otp' => $this->otp,
                        'title' => $this->title,
                        'name' => $this->name,
                    ]);
    }
}
