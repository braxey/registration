<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        $startTime = $this->faker->dateTimeBetween('+1 day', '+1 week');
        $endTime = clone $startTime;
        $endTime->modify('+1 hour');
    
        return [
            'description' => $this->faker->text(100),
            'start_time'  => $startTime,
            'end_time'    => $endTime,
            'total_slots' => $this->faker->numberBetween(1, 10),
        ];
    }
}
