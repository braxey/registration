<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\WalkIn;
use App\Models\Appointment;

class WalkInFactory extends Factory
{
    protected $model = WalkIn::class;

    public function definition(): array
    {
        return [
            'email'          => $this->faker->email,
            'name'           => $this->faker->name,
            'slots'          => $this->faker->numberBetween(1, 10),
            'desired_time'   => now('EST'),
            'appointment_id' => null,
            'notes'          => $this->faker->text(100),
            'notified'       => 0,
        ];
    }

    public function withoutEmail()
    {
        return $this->state([
            'email' => ''
        ]);
    }

    public function withSlots(int $slots)
    {
        return $this->state([
            'slots' => $slots
        ]);
    }

    public function withDesiredTime($time)
    {
        return $this->state([
            'desired_time' => $time
        ]);
    }

    public function withAppointment(Appointment $appointment)
    {
        return $this->state([
            'appointment_id' => $appointment->getId()
        ]);
    }
}
