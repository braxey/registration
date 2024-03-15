<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Constants\EmailTypes;
use App\Models\QueuedEmail;

class QueueService
{
    private MailerService $mailer;

    private Collection $queuedEmails;

    private int $maxSendsPerHour;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function push(string $to, int $type, array $payload): void
    {
        $email = new QueuedEmail();
        $email->to_address = $to;
        $email->email_type = $type;
        $email->payload = json_encode($payload);
        $email->save();
    }

    public function handleQueueDispatch(): void
    {
        $this->setQueuedEmails();
        $this->sendAllPossibleQueuedEmails();
        $this->deleteAllPossibleQueuedEmails();
    }

    private function setQueuedEmails(): void
    {
        $this->queuedEmails = QueuedEmail::orderBy('created_at')->get();
    }

    private function getNumberOfEmailsSentInThePastHour(): int
    {
        return $this->queuedEmails->filter(function (QueuedEmail $queued) {
            return $queued->wasSent() && $queued->wasSentLessThanAnHourAgo();
        })->count();
    }

    private function sendAllPossibleQueuedEmails(): void
    {
        $sentWithinPastHour = $this->getNumberOfEmailsSentInThePastHour();
        $maxPerHour = config('mail.max-per-hour');
        $mailer = $this->mailer;

        $this->queuedEmails->filter(function (QueuedEmail $queued) {
            return $queued->wasNotSent();
        })->sortByDesc(function (QueuedEmail $queued) {
            if ($queued->getEmailType() === EmailTypes::VERIFICATION) {
                return 1;
            }

            return 0;
        })->each(function (QueuedEmail $queued) use (&$sentWithinPastHour, $maxPerHour, $mailer) {
            if ($sentWithinPastHour >= $maxPerHour) {
                return false;
            }

            $mailer->sendFromQueue($queued);
            $queued->markSent();
            $sentWithinPastHour++;
        });
    }

    private function deleteAllPossibleQueuedEmails(): void
    {
        $this->queuedEmails->filter(function (QueuedEmail $queued) {
            return $queued->wasSent();
        })->each(function (QueuedEmail $queued) {
            if (! $queued->wasSentLessThanAnHourAgo()) {
                $queued->delete();
            }
        });
    }
}
