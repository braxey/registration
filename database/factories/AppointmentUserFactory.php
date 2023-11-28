<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Appointment;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppointmentUser>
 */
class AppointmentUserFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => function () {
                return User::factory()->create()->getId();
            },
            'appointment_id' => function () {
                return Appointment::factory()->create()->getId();
            },
            'slots_taken' => function () {
                return 2;
            },
            'notified' => function () {
                return 0;
            }
        ];
    }

    public function forUser(User $user)
    {
        return $this->state([
            'user_id' => $user->getId(),
        ]);
    }

    public function forAppointment(Appointment $appointment)
    {
        return $this->state([
            'appointment_id' => $appointment->getId(),
        ]);
    }

    public function withSlots(int $slots)
    {
        return $this->state([
            'slots_taken' => $slots,
        ]);
    }

    public function asNotified(bool $notified)
    {
        return $this->state([
            'notified' => $notified,
        ]);
    }
}
