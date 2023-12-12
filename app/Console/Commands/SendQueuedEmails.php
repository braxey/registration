<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MailerService;
use App\Models\QueuedEmail;

class SendQueuedEmails extends Command
{
    protected $signature = 'app:send-queued-emails';

    protected $description = 'Send emails in the queue';

    protected $mailer;

    public function __construct(MailerService $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    public function handle()
    {
        $queuedEmails = QueuedEmail::orderBy('created_at')->get();
        $maxPerHour = config('mail.max-per-hour');
        $mailer = $this->mailer;

        $sentWithinPastHour = $queuedEmails->filter(function (QueuedEmail $queued) {
            return $queued->isQueuedForLessThanAnHour() && $queued->wasSent();
        })->count();

        // send any emails we can
        $queuedEmails->each(function (QueuedEmail $queued) use (&$sentWithinPastHour, $maxPerHour, $mailer) {
            if ($sentWithinPastHour >= $maxPerHour) {
                return false;
            }

            if ($queued->wasNotSent()) {
                $mailer->sendFromQueue($queued);
                $queued->markSent();
            }
        });

        // delete items queued for over an hour that were already sent
        $queuedEmails->each(function (QueuedEmail $queued) {
            if ($queued->isQueuedForLessThanAnHour()) {
                return false;
            }

            if ($queued->isQueuedForOverAnHour() && $queued->wasSent()) {
                $queued->delete();
            }
        });
    }
}
