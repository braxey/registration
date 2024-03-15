<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QueueService;
use App\Constants\EmailTypes;
use App\Models\QueuedEmail;

class SendQueuedEmails extends Command
{
    protected $signature = 'app:send-queued-emails';

    protected $description = 'Send emails in the queue';

    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        parent::__construct();
        $this->queueService = $queueService;
    }

    public function handle()
    {
        $this->queueService->handleQueueDispatch();
    }
}
