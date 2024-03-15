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
        config(['mail.max-per-hour' => 10]); // set a rate limit of 10 emails/hr
    }

    public function testQueueServiceCanHandleAnEmptyQueue()
    {
        $this->queueService->handleQueueDispatch();
        $this->assertCount(0, QueuedEmail::all());
    }

    public function testQueueServiceCanDispatchWithAllEmailTypes()
    {
        $this->seedNotificationEmails(1);
        $this->seedVerificationEmails(1);
        $this->seedCustomEmails(1);

        $this->queueService->handleQueueDispatch();

        $queuedEmails = QueuedEmail::all();
        $this->assertCount(3, $queuedEmails); // total records
        $this->assertCount(3, $queuedEmails->where('sent', 1)); // sent records
    }

    public function testQueueServiceRespectsRateLimit()
    {
        $this->seedNotificationEmails(5);
        $this->seedVerificationEmails(5);
        $this->seedCustomEmails(5);

        $this->queueService->handleQueueDispatch();

        $queuedEmails = QueuedEmail::all();
        $this->assertCount(10, $queuedEmails->where('sent', 1));
        $this->assertCount(5, $queuedEmails->where('sent', 0));

        // dispatching again should have no affect
        $this->queueService->handleQueueDispatch();

        $queuedEmails = QueuedEmail::all();
        $this->assertCount(10, $queuedEmails->where('sent', 1));
        $this->assertCount(5, $queuedEmails->where('sent', 0));
    }

    public function testEmailsOfTheSameTypeAreSentBasedOnTimeQueued()
    {
        config(['mail.max-per-hour' => 1]); // simulate rate of 1 emails/hr so we can see the send order

        $this->seedNotificationEmails(25);
        $expectedSendOrder = QueuedEmail::orderBy('created_at')->get();

        foreach ($expectedSendOrder as $expected) {
            $this->queueService->handleQueueDispatch();

            // make sure we're sending in the expected order
            $entireQueue = QueuedEmail::all();
            $sentEmail = $entireQueue->where('sent', 1)->first();
            $this->assertCount(1, $entireQueue->where('sent', 1));
            $this->assertCount($entireQueue->count() - 1, $entireQueue->where('sent', 0));
            $this->assertSame($expected->id, $sentEmail->id);

            // delete the sent email so it doesn't seem like our rate limit was hit
            $sentEmail->delete();
        }
    }

    public function testQueueServiceFavorsVerificationEmails()
    {
        $this->seedNotificationEmails(9);
        $this->seedVerificationEmails(9);
        $this->seedCustomEmails(9);

        $this->queueService->handleQueueDispatch();

        $queuedEmails = QueuedEmail::all();
        $sentEmails = $queuedEmails->where('sent', 1);
        $this->assertCount(10, $sentEmails); // 10 emails sent
        $this->assertCount(9, $sentEmails->where('email_type', EmailTypes::VERIFICATION)); // all the verification emails sent
    }

    // test queued emails over an hour arent deleted if they weren't sent
    public function testQueuedEmailsOverAnHourArentDeletedIfTheyWerentSent()
    {
        // config(['mail.max-per-hour' => 2]);

        // QueuedEmail::factory()->create();
        // QueuedEmail::factory()->asQueuedOverAnHourAgo()->create();
        // QueuedEmail::factory()->asQueuedOverAnHourAgo()->create();

        // $this->queueService->handleQueueDispatch();

        // $queuedEmails = QueuedEmail::all();
        // $sentEmails = $queuedEmails->where('sent', 1);

        // // 2 should've been sent, but the sent email queued over an hour ago should've been deleted
        // $this->assertCount(10, $sentEmails);
        // $this->assertCount(9, $sentEmails->where('email_type', EmailTypes::VERIFICATION));

        // however, the email queued over an hour ago
    }

    // test unneeded queued emails are purged

    private function seedNotificationEmails(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            QueuedEmail::factory()->asNotificationEmail()->create();
        }
    }

    private function seedVerificationEmails(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            QueuedEmail::factory()->asVerificationEmail()->create();
        }
    }

    private function seedCustomEmails(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            QueuedEmail::factory()->asCustomEmail($this->user->getId())->create();
        }
    }
}
