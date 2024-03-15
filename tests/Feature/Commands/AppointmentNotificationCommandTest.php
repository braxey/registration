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

class AppointmentNotificationCommandTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected const COMMAND_SIGNATURE = 'notify:upcoming-appointments';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function testUsersWithUpcomingAppointmentsDontGetNotifiedIfTheAppointmentIsOverAnHourAway()
    {
        $appointment = Appointment::factory()->withTotalSlots(15)->create();

        $booking = AppointmentUser::factory()->withSlots(5)->forUser($this->user)->forAppointment($appointment)->create();
        $walkIn = WalkIn::factory()->withSlots(5)->withAppointment($appointment)->create();

        Artisan::call(static::COMMAND_SIGNATURE);

        $booking->refresh();
        $walkIn->refresh();

        $this->assertCount(0, QueuedEmail::all()); // no emails were added to the queue
        $this->assertTrue($booking->userWasNotNotified());
        $this->assertTrue($walkIn->wasNotNotified());
    }

    public function testUsersDontGetNotifiedIfTheyWereAlreadyNotified()
    {
        $appointment = Appointment::factory()->withTotalSlots(15)->withinTheNextHour()->create();

        $booking = AppointmentUser::factory()->withSlots(5)->forUser($this->user)->forAppointment($appointment)->create();
        $walkIn = WalkIn::factory()->withSlots(5)->withAppointment($appointment)->create();

        // mark the booking and walk-in as notified
        $booking->markAsNotified();
        $walkIn->markAsNotified();

        Artisan::call(static::COMMAND_SIGNATURE);

        $booking->refresh();
        $walkIn->refresh();

        $this->assertCount(0, QueuedEmail::all()); // no emails were added to the queue
        $this->assertTrue($booking->userWasNotified());
        $this->assertTrue($walkIn->wasNotified());
    }

    public function testUsersGetNotified()
    {
        $appointment = Appointment::factory()->withTotalSlots(15)->withinTheNextHour()->create();

        $booking = AppointmentUser::factory()->withSlots(5)->forUser($this->user)->forAppointment($appointment)->create();
        $walkIn = WalkIn::factory()->withSlots(5)->withAppointment($appointment)->create();

        Artisan::call(static::COMMAND_SIGNATURE);

        $booking->refresh();
        $walkIn->refresh();

        $this->assertCount(2, QueuedEmail::all()); // no emails were added to the queue
        $this->assertTrue($booking->userWasNotified());
        $this->assertTrue($walkIn->wasNotified());
    }
}
