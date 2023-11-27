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

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->appointment = Appointment::factory()->create();
    }

    /* ========== BOOK ========== */

    // testUsersCanGetBookingPage (assert view has: appointment, availableSlots, userSlots, organization)
    // testRedirectToEditBookingIfGettingBookingPageWhenUserAppointmentExists
    // testUserCanBookAppointment (assert database has: userAppointment, user has correct number of slots_booked, appointment has correct number of slots_taken)
    // testSlotValidationForBooking (cannot be < 1, cannot be decimal, cannot be string, cannot be greater than available slots)
    // testBookingBreachesMaxSlotsPerUserForOrganization

    /* ========== EDIT BOOKING ========== */

    // testUsersCanGetEditBookingPage (assert view has: appointment, availableSlots, userSlots, bookingSlots, organization)
    // testRedirectToBookingIfGettingEditBookingPageWhenUserAppointmentDoesNotExist
    // testUserCanEditBookingForAppointment (assert database has: userAppointment, user has correct number of slots_booked, appointment has correct number of slots_taken)
    // testSlotValidationForEditBooking (cannot be < 0, cannot be decimal, cannot be string, cannot be greater than available slots + user slots)
    // testBookingCancelsWhenEditingSlotsToZero
    // testEditBookingBreachesMaxSlotsPerUserForOrganization

    /* ========== CANCEL BOOKING ========== */

    // testCannotCancelBookingThatDoesNotExist
    // testBookingCanBeCanceled
}
