<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyEmail;
use App\Mail\VerificationEmail;
use App\Mail\CustomEmail;
use Illuminate\Support\Collection;
use App\Models\User;

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

    public function sendCustomEmail(User $recipient, string $subject, string $message, bool $includeAppointments, ?Collection $appointments = null)
    {
        $mail = new CustomEmail($recipient, $subject, $message, $includeAppointments, $appointments);
        $this->send($recipient->getEmail(), $mail);
    }

    private function send(string $to, Mailable $mail)
    {
        Mail::to($to)->send($mail);
    }
}
