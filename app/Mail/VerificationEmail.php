<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->from('donotreply@wtbregistration.com', 'WTB Registration')
                    ->view('emails.verify-email', ['code' => $this->code])
                    ->subject('WTB Verification Code');
    }
}
