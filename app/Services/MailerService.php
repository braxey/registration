<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use App\Constants\EmailTypes;
use App\Mail\NotifyEmail;
use App\Mail\VerificationEmail;
use App\Mail\CustomEmail;
use App\Models\User;
use App\Models\QueuedEmail;
use Carbon\Carbon;

class MailerService
{
    private function sendVerificationEmail(string $to, array $payload)
    {
        $mail = new VerificationEmail($payload['token']);
        $this->send($to, $mail);
    }

    private function sendNotificationEmail(string $to, array $payload)
    {
        $mail = new NotifyEmail(Carbon::parse($payload['date-time'], 'EST'), $payload['slots'], $payload['name'], $payload['update']);
        $this->send($to, $mail);
    }

    private function sendCustomEmail(string $to, array $payload)
    {
        $mail = new CustomEmail($payload);
        $this->send($to, $mail);
    }

    private function send(string $to, Mailable $mail)
    {
        Mail::to($to)->send($mail);
    }

    public function sendFromQueue(QueuedEmail $queued)
    {
        $type = $queued->getEmailType();
        $to = $queued->getTo();
        $payload = $queued->getPayload();

        switch ($type) {
            case EmailTypes::VERIFICATION:
                return $this->sendVerificationEmail($to, $payload);
            case EmailTypes::NOTIFICATION:
                return $this->sendNotificationEmail($to, $payload);
            case EmailTypes::CUSTOM:
                return $this->sendCustomEmail($to, $payload);
            default:
                return;
        }
    }
}
