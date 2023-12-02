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

    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function build()
    {
        return $this->from('donotreply@wtbregistration.com', 'WTB Registration')
                    ->view('emails.verify-email', ['code' => $this->token])
                    ->subject('WTB Verification Code');
    }

    public function assertTokensMatch(string $token): bool
    {
        return $this->token === $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
