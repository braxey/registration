<?php

namespace Database\Factories;

use App\Models\AppointmentUser;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentUserFactory extends Factory
{
    protected $model = AppointmentUser::class;

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
