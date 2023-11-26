<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyEmail;
use App\Mail\VerificationEmail;

class MailerService
{
    public function sendVerificationEmail(string $to, string $token)
    {
        $mail = new VerificationEmail($token);
        $this->send($to, $mail);
    }

    public function sendNotificationEmail(string $to, array $payload)
    {
        $mail = new NotifyEmail($payload['date-time'], $payload['slots'], $payload['name'], $payload['update']);
        $this->send($to, $mail);
    }

    private function send(string $to, Mailable $mail)
    {
        Mail::to($to)->send($mail);
    }
}
