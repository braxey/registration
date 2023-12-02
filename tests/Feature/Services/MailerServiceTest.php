<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\MailableFake;
use App\Services\MailerService;
use App\Mail\NotifyEmail;
use App\Mail\VerificationEmail;
use Tests\TestCase;
use Faker\Factory as FakerFactory;
use Carbon\Carbon;

class MailerServiceTest extends TestCase
{
    protected $mailer;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->mailer = new MailerService();
        $this->faker = FakerFactory::create();
    }

    public function testSendVerificationEmail()
    {
        $token = generateSecureNumericToken();
        $to = $this->faker->email;
        $this->mailer->sendVerificationEmail($to, $token);

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

        $this->mailer->sendNotificationEmail($to, $payload);

        Mail::assertSent(NotifyEmail::class, function ($mail) use ($to, $payload) {
            $this->assertTrue($mail instanceof NotifyEmail);
            $mail->assertHas($payload);
            return $mail->hasTo($to);
        });
    }
}
