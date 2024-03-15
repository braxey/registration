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

class QueueService
{
    private MailerService $mailer;

    private Collection $queuedEmails;

    private int $maxSendsPerHour;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
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
            return $queued->isQueuedForLessThanAnHour() && $queued->wasSent();
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
            if ($queued->isQueuedForLessThanAnHour()) {
                return false;
            }

            if ($queued->isQueuedForOverAnHour() && $queued->wasSent()) {
                $queued->delete();
            }
        });
    }
}
