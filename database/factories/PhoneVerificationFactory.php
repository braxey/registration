<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PhoneVerification>
 */
class PhoneVerificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => generateSecureNumericToken(),
            'time_sent' => Carbon::now('EST'),
            'user_id' => 1
        ];
    }

    public function withUser(User $user)
    {
        return $this->state([
            'user_id' => $user->getId()
        ]);
    }
}
