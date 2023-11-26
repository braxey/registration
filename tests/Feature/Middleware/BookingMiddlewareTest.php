<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Organization;

class BookingMiddlewareTest extends TestCase
{
    private Organization $organization;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        session(['dry-run' => true]);
        $this->organization = Organization::find(1);
        $this->user = User::factory()->create();
    }

    // testRedirectToIndexIfRegistrationIsClosedForOrganization
    // testCannotPassIfAppointmentIsNotFound
    // testCannotPassIfPastNoonOnDayOfAppointment
    // testCannotPassIfAppointmentIsWalkInOnly
    // testCanPass
}
