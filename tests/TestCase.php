<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.gilgamesh' => 9999]);
    }

    /* ===== HELPERS ===== */

    protected function setUpBooking(User $user, Appointment $appointment, int $slots): AppointmentUser
    {
        $user->incrementSlotsBooked($slots);
        $appointment->incrementSlotsTaken($slots);
        return AppointmentUser::factory()->withSlots($slots)->forUser($user)->forAppointment($appointment)->create();
    }

    protected function addAppointments()
    {
        for ($i = 0; $i < 3; $i++) {
            Appointment::factory()->create([
                'status' => 'upcoming',
                'start_time' => now('EST')->addDays(2)->addMinutes($i),
                'end_time' => now('EST')->addDays(2)->addHours(1)
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            Appointment::factory()->create([
                'status' => 'completed',
                'start_time' => now('EST')->subDays(3)->addMinutes($i),
                'end_time' => now('EST')->subDays(3)->addHours(1),
                'past_end' => true
            ]);
        }
        
        for ($i = 0; $i < 3; $i++) {
            Appointment::factory()->create([
                'status' => 'in progress',
                'start_time' => now('EST')->subMinutes($i),
                'end_time' => now('EST')->addHours(1)
            ]);
        }
    }
}
