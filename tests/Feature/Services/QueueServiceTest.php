<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\MailableFake;
use App\Models\User;
use App\Models\QueuedEmail;
use App\Services\QueueService;
use App\Constants\EmailTypes;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use Carbon\Carbon;

class QueueServiceTest extends TestCase
{
    use RefreshDatabase;

    protected QueueService $queueService;

    protected $faker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->queueService = app(QueueService::class);
        $this->faker = FakerFactory::create();
        $this->user = User::factory()->create();
    }

    public function testQueueServiceCanHandleAnEmptyQueue()
    {
        $this->queueService->handleQueueDispatch();
        $this->assertSame(0, QueuedEmail::all()->count());
    }

    public function testQueueServiceCanDispatchWithAllEmailTypes()
    {
        QueuedEmail::factory()->create(); // default is a notification email
        QueuedEmail::factory()->asVerificationEmail()->create();
        QueuedEmail::factory()->asCustomEmail($this->user->getId())->create();

        $this->queueService->handleQueueDispatch();

        $queuedEmails = QueuedEmail::all();
        $this->assertSame(3, $queuedEmails->count()); // total records
        $this->assertSame(3, $queuedEmails->where('sent', 1)->count()); // sent records
    }

    // test emails of the same type are sent based on time queued
    


    // test queue service respects rate limit
    // test queue service favors verification emails
    // test queued emails over an hour arent deleted if they weren't sent
    // test unneeded queued emails are purged
}
