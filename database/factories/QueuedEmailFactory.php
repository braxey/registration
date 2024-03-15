<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\QueuedEmail;
use App\Constants\EmailTypes;
use Carbon\Carbon;

class QueuedEmailFactory extends Factory
{
    protected $model = QueuedEmail::class;

    public function definition(): array
    {
        return [
            'to_address' => $this->faker->email,
            'sent'       => 0,
            'email_type' => EmailTypes::NOTIFICATION,
            'payload'    => json_encode([
                'date-time' => Carbon::parse($this->faker->dateTime),
                'slots'     => $this->faker->numberBetween(1, 10),
                'name'      => $this->faker->name,
                'update'    => $this->faker->boolean,
            ]),
        ];
    }

    public function asVerificationEmail()
    {
        return $this->state([
            'email_type' => EmailTypes::VERIFICATION,
            'payload'    => json_encode([
                'token' => generateSecureNumericToken()
            ])
        ]);
    }

    public function asCustomEmail(int $userId)
    {
        return $this->state([
            'email_type' => EmailTypes::CUSTOM,
            'payload'    => json_encode([
                'userId'  => $userId,
                'include-appointment-details' => false,
                'subject' => $this->faker->text(25),
                'message' => $this->faker->text(500),
            ])
        ]);
    }

    public function asSent()
    {
        return $this->state([
            'sent' => 1
        ]);
    }

    public function asQueuedOverAnHourAgo()
    {
        return $this->state([
            'created_at' => now('EST')->modify('-2 hours')
        ]);
    }
}
