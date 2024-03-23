<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\WalkIn;
use App\Models\QueuedEmail;

class UpdateAppointmentStatusCommandTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected const COMMAND_SIGNATURE = 'appointments:update-status';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function testUpcomingAppointmentsDontGetUpdated()
    {
        $appointment = Appointment::factory()->create();

        Artisan::call(static::COMMAND_SIGNATURE);

        $appointment->refresh();

        $this->assertTrue($appointment->isUpcoming());
    }

    public function testAppointmentsGetUpdatedFromUpcomingToInProgress()
    {
        $appointment = Appointment::factory()->withInProgressStartTime()->create();
        $this->assertTrue($appointment->isUpcoming());

        Artisan::call(static::COMMAND_SIGNATURE);

        $appointment->refresh();

        $this->assertTrue($appointment->isInProgress());
    }

    public function testAppointmentsEndAndUsersGetSlotsBack()
    {
        $appointment = Appointment::factory()->inProgress()->withCompletedStartTime()->create();
        $this->setUpBooking($this->user, $appointment, 3);
        $this->assertSame(3, $this->user->getCurrentNumberOfSlots());

        Artisan::call(static::COMMAND_SIGNATURE);

        $appointment->refresh();
        $this->assertTrue($appointment->isCompleted());
        $this->assertTrue($appointment->pastEnd());

        $this->user->refresh();
        $this->assertSame(0, $this->user->getCurrentNumberOfSlots());
    }
}
