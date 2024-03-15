<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\MailableFake;
use App\Models\QueuedEmail;
use App\Services\MailerService;
use App\Services\QueueService;
use App\Mail\NotifyEmail;
use App\Mail\VerificationEmail;
use App\Constants\EmailTypes;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use Carbon\Carbon;

class MailerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MailerService $mailer;

    protected QueueService $queueService;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->mailer = app(MailerService::class);
        $this->queueService = app(QueueService::class);
        $this->faker = FakerFactory::create();
    }

    public function testSendVerificationEmail()
    {
        $token = generateSecureNumericToken();
        $to = $this->faker->email;

        $payload = ['token' => $token];
        $this->queueService->push($to, EmailTypes::VERIFICATION, $payload);

        $queuedEmail = QueuedEmail::orderByDesc('id')->first();
        $this->mailer->sendFromQueue($queuedEmail);

        Mail::assertSent(VerificationEmail::class, function ($mail) use ($to, $token) {
            $this->assertTrue($mail instanceof VerificationEmail);
            $mail->assertTokensMatch($token);
            return $mail->hasTo($to);
        });
    }

    public function testSendNotificationEmail()
    {
        $to = $this->faker->email;
        $payload = [
            'date-time' => Carbon::parse($this->faker->dateTime),
            'slots'     => $this->faker->numberBetween(1, 10),
            'name'      => $this->faker->name,
            'update'    => $this->faker->boolean,
        ];

        $this->queueService->push($to, EmailTypes::NOTIFICATION, $payload);

        $queuedEmail = QueuedEmail::orderByDesc('id')->first();
        $this->mailer->sendFromQueue($queuedEmail);

        Mail::assertSent(NotifyEmail::class, function ($mail) use ($to, $payload) {
            $this->assertTrue($mail instanceof NotifyEmail);
            $mail->assertHas($payload);
            return $mail->hasTo($to);
        });
    }
}
