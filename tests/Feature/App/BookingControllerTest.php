<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Organization;
use App\Models\AppointmentUser;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Appointment $appointment;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::find(1);
        $this->organization->openRegistration();
        $this->user = User::factory()->create();
        $this->appointment = Appointment::factory()->create();
    }

    /* ========== BOOK ========== */

    public function testUsersCanGetBookingPage()
    {
        $response = $this->actingAs($this->user)->get(route('booking.get-booking', $this->appointment->getId()));
        $response->assertSuccessful();

        $response->assertViewHas('appointment');
        $response->assertViewHas('organization');

        $seenAppointment = $response->viewData('appointment');
        $seenOrganization = $response->viewData('organization');

        $this->assertTrue($seenAppointment->is($this->appointment));
        $this->assertTrue($seenOrganization->is($this->organization));
    }

    public function testRedirectToEditBookingIfGettingBookingPageWhenAppointmentUserExists()
    {
        $slots = 2;
        $booking = AppointmentUser::factory()->withSlots($slots)->forUser($this->user)->forAppointment($this->appointment)->create();
        $this->user->incrementSlotsBooked($slots);
        $this->appointment->incrementSlotsTaken($slots);

        $this->actingAs($this->user)
            ->get(route('booking.get-booking', $this->appointment->getId()))
            ->assertRedirect(route('booking.get-edit-booking', $this->appointment->getId()));
    }

    // testUserCanBookAppointment (assert database has: userAppointment, user has correct number of slots_booked, appointment has correct number of slots_taken)
    // testSlotValidationForBooking (cannot be < 1, cannot be decimal, cannot be string, cannot be greater than available slots)
    // testBookingBreachesMaxSlotsPerUserForOrganization

    /* ========== EDIT BOOKING ========== */

    // testUsersCanGetEditBookingPage (assert view has: appointment, availableSlots, userSlots, bookingSlots, organization)
    // testRedirectToBookingIfGettingEditBookingPageWhenAppointmentUserDoesNotExist
    // testUserCanEditBookingForAppointment (assert database has: userAppointment, user has correct number of slots_booked, appointment has correct number of slots_taken)
    // testSlotValidationForEditBooking (cannot be < 0, cannot be decimal, cannot be string, cannot be greater than available slots + user slots)
    // testBookingCancelsWhenEditingSlotsToZero
    // testEditBookingBreachesMaxSlotsPerUserForOrganization

    /* ========== CANCEL BOOKING ========== */

    // testCannotCancelBookingThatDoesNotExist
    // testBookingCanBeCanceled
}
