<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;

class MailerService
{
    public function sendVerificationEmail(string $to, string $token)
    {
        $mail = new VerificationEmail($token);
        $this->send($to, $mail);
    }

    private function send(string $to, Mailable $mail)
    {
        Mail::to($to)->send($mail);
    }
}
