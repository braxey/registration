<?php

namespace Tests\Feature;

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
        $this->appointment = Appointment::factory()->withTotalSlots(15)->create();
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
        $booking = $this->setUpBooking($this->user, $this->appointment, 2);

        $this->actingAs($this->user)
            ->get(route('booking.get-booking', $this->appointment->getId()))
            ->assertRedirect(route('booking.get-edit-booking', $this->appointment->getId()));
    }

    public function testUserCanBookAppointment()
    {
        $payload = ['slots' => 2];
        $this->actingAs($this->user)
            ->post(route('booking.book', $this->appointment->getId()), $payload)
            ->assertRedirect(route('dashboard'));

        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        $this->assertTrue($booking->getSlotsTaken() === 2);
        $this->assertTrue($booking->getUser()->is($this->user));
        $this->assertTrue($booking->getAppointment()->is($this->appointment));
    }

    public function testCannotBookIfBookingAlreadyExists()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 2);

        $this->actingAs($this->user)
            ->post(route('booking.book', $this->appointment->getId()))
            ->assertUnauthorized();
    }

    public function testSlotValidationForBooking()
    {
        // slots < 1
        $payload = ['slots' => 0];
        $this->actingAs($this->user)
            ->post(route('booking.book', $this->appointment->getId()), $payload)
            ->assertRedirect();
        $this->assertNull(AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId()));

        // slots is a decimal
        $payload = ['slots' => 3.14];
        $this->actingAs($this->user)
            ->post(route('booking.book', $this->appointment->getId()), $payload)
            ->assertRedirect();
        $this->assertNull(AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId()));

        // slots is a string
        $payload = ['slots' => 'a string'];
        $this->actingAs($this->user)
            ->post(route('booking.book', $this->appointment->getId()), $payload)
            ->assertRedirect();
        $this->assertNull(AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId()));
    }
    
    public function testBookingBreachesMaxSlotsPerUserForOrganization()
    {
        $otherSlots = $this->organization->getMaxSlotsPerUser() - 2;
        $otherAppointment = Appointment::factory()->withTotalSlots(15)->create();
        $this->setUpBooking($this->user, $otherAppointment, $otherSlots);

        $payload = ['slots' => 3];
        $this->actingAs($this->user)
            ->post(route('booking.book', $this->appointment->getId()), $payload)
            ->assertRedirect();

        $this->assertNull(AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId()));
    }

    /* ========== EDIT BOOKING ========== */

    public function testUsersCanGetEditBookingPage()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 2);

        $response = $this->actingAs($this->user)
            ->get(route('booking.get-edit-booking', $this->appointment->getId()))
            ->assertSuccessful()
            ->assertViewHas(['appointment', 'bookingSlots', 'userSlots', 'organization']);

        $this->assertTrue(
            $response->viewData('appointment')->is($this->appointment)
            && $response->viewData('bookingSlots') === $booking->getSlotsTaken()
            && $response->viewData('userSlots') === $this->user->getCurrentNumberOfSlots()
            && $response->viewData('organization')->is($this->organization)
        );
    }

    public function testRedirectToBookingIfGettingEditBookingPageWhenBookingDoesNotExist()
    {
        $this->actingAs($this->user)
            ->get(route('booking.get-edit-booking', $this->appointment->getId()))
            ->assertRedirect(route('booking.get-booking', $this->appointment->getId()));
    }

    public function testUserCanEditBookingForAppointment()
    {
        $this->setUpBooking($this->user, $this->appointment, 2);

        $payload = ['slots' => 4];
        $response = $this->actingAs($this->user)
            ->put(route('booking.edit-booking', $this->appointment->getId()), $payload)
            ->assertRedirect(route('dashboard'));
        
        $user = User::fromId($this->user->getId());
        $appointment = Appointment::fromId($this->appointment->getId());
        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());

        $this->assertTrue(
            $user->getCurrentNumberOfSlots() === 4
            && $appointment->getSlotsTaken() === 4
            && $booking->getSlotsTaken() === 4
        );
    }

    public function testSlotValidationForEditBooking()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 2);

        // slots < 0
        $payload = ['slots' => -1];
        $this->actingAs($this->user)
            ->put(route('booking.edit-booking', $this->appointment->getId()), $payload)
            ->assertRedirect();
        $updatedBooking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        $this->assertTrue($booking->is($updatedBooking));

        // slots is a decimal
        $payload = ['slots' => 3.14];
        $this->actingAs($this->user)
            ->put(route('booking.edit-booking', $this->appointment->getId()), $payload)
            ->assertRedirect();
        $updatedBooking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        $this->assertTrue($booking->is($updatedBooking));

        // slots is a string
        $payload = ['slots' => 'S T R I N G'];
        $this->actingAs($this->user)
            ->put(route('booking.edit-booking', $this->appointment->getId()), $payload)
            ->assertRedirect();
        $updatedBooking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        $this->assertTrue($booking->is($updatedBooking));
    }

    public function testEditBookingBreachesMaxSlotsPerUserForOrganization()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 3);
        $payload = ['slots' => 4];
        $this->actingAs($this->user)
            ->put(route('booking.edit-booking', $this->appointment->getId()), $payload)
            ->assertRedirect();
        $updatedBooking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        $this->assertTrue($booking->is($updatedBooking));
    }

    public function testBookingCancelsWhenEditingSlotsToZero()
    {
        $this->setUpBooking($this->user, $this->appointment, 3);
        $payload = ['slots' => 0];
        $this->actingAs($this->user)
            ->put(route('booking.edit-booking', $this->appointment->getId()), $payload)
            ->assertRedirect(route('appointments.index'));
        
        $user = User::fromId($this->user->getId());
        $appointment = Appointment::fromId($this->appointment->getId());
        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());

        $this->assertTrue(
            $user->getCurrentNumberOfSlots() === 0
            && $appointment->getSlotsTaken() === 0
            && $booking === null
        );
    }

    /* ========== CANCEL BOOKING ========== */

    public function testBookingCanBeCanceled()
    {
        $this->setUpBooking($this->user, $this->appointment, 3);
        $this->actingAs($this->user)
            ->post(route('booking.cancel-booking', $this->appointment->getId()))
            ->assertRedirect(route('dashboard'));
        
        $user = User::fromId($this->user->getId());
        $appointment = Appointment::fromId($this->appointment->getId());
        $booking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());

        $this->assertTrue(
            $user->getCurrentNumberOfSlots() === 0
            && $appointment->getSlotsTaken() === 0
            && $booking === null
        );
    }

    public function testCannotCancelBookingThatDoesNotExist()
    {
        $this->actingAs($this->user)
            ->post(route('booking.cancel-booking', $this->appointment->getId()))
            ->assertNotFound();
    }
}
