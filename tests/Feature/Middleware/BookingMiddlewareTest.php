<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Organization;
use Carbon\Carbon;

/**
 * @see BookingMiddleware
 */
class BookingMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private User $user;
    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();
        session(['booking-dry-run' => true]);
        $this->organization = Organization::find(1);
        $this->organization->openRegistration();
        $this->user = User::factory()->create();
        $this->appointment = Appointment::factory()->create();
    }

    public function testRedirectToIndexIfRegistrationIsClosedForOrganization()
    {
        $this->organization->closeRegistration();
        $this->actingAs($this->user)
            ->get(route('booking.get-booking', $this->appointment->getId()))
            ->assertRedirect(route('appointments.index'));
    }

    public function testCannotPassIfAppointmentIsNotFound()
    {
        $this->actingAs($this->user)
            ->get(route('booking.get-booking', $this->appointment->getId() + 1))
            ->assertNotFound()
            ->assertSee('appointment not found');
    }

    public function testCannotPassIfPastNoonOnDayOfAppointment()
    {
        Carbon::setTestNow(Carbon::now('EST')->startOfDay()->addHours(13));
        $appointment = Appointment::factory()->todayAtHour(14)->create();
        $this->actingAs($this->user)
            ->get(route('booking.get-booking', $appointment->getId()))
            ->assertUnauthorized()
            ->assertSee('too late to book');
        Carbon::setTestNow();
    }

    public function testCannotPassIfAppointmentIsWalkInOnly()
    {
        $appointment = Appointment::factory()->asWalkInOnly()->create();
        $this->actingAs($this->user)
            ->get(route('booking.get-booking', $appointment->getId()))
            ->assertUnauthorized()
            ->assertSee('appointment is walk-in-only');
    }

    public function testCanPass()
    {
        $this->actingAs($this->user)
            ->get(route('booking.get-booking', $this->appointment->getId()))
            ->assertStatus(202)
            ->assertSee('passes booking middleware');
    }
}
