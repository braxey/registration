<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Organization;
use App\Models\AppointmentUser;

/**
 * @see AdminBookingController
 */
class AdminBookingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Appointment $appointment;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::find(1);
        $this->organization->openRegistration();
        $this->actingAs($this->admin = User::factory()->admin()->create());
        $this->user = User::factory()->create();
        $this->appointment = Appointment::factory()->withTotalSlots(15)->create();
    }

    public function testGetLandingPage()
    {
        $this->get(route('admin-booking.get-lookup'))->assertOk();
    }

    /* ========== GET USER'S UPCOMING BOOKINGS ========== */

    public function testGetUsersUpcomingBookings()
    {
        $anotherAppointment = Appointment::factory()->withTotalSlots(5)->create();
        $completedAppointment = Appointment::factory()->withTotalSlots(5)->pastEnd()->create();
        $this->setUpBooking($this->user, $this->appointment, 2);
        $this->setUpBooking($this->user, $anotherAppointment, 3);
        $this->setUpBooking($this->user, $completedAppointment, 5);
        $userUpcomingAppointments = $this->user->getUpcomingAppointments();

        $response = $this->get(route('admin-booking.user', $this->user->getId()))
                        ->assertSuccessful()
                        ->assertViewHas(['user', 'appointments']);

        $this->assertTrue($response->viewData('user')->is($this->user));
        $this->assertEquals($response->viewData('appointments'), $userUpcomingAppointments);
        $this->assertCount(2, $userUpcomingAppointments);
    }

    /* ========== EDIT BOOKING ========== */

    public function testAdminCanGetUsersEditBookingPage()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 2);

        $response = $this->get(route('admin-booking.user-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]))
        ->assertSuccessful()
        ->assertViewHas(['user', 'appointment', 'organization', 'userSlots', 'bookingSlots']);

        $this->assertTrue(
            $response->viewData('user')->is($this->user)
            && $response->viewData('appointment')->is($this->appointment)
            && $response->viewData('bookingSlots') === $booking->getSlotsTaken()
            && $response->viewData('userSlots') === $this->user->getCurrentNumberOfSlots()
            && $response->viewData('organization')->is($this->organization)
        );
    }

    public function test404IfGettingEditBookingPageWhenBookingDoesNotExist()
    {
        $this->get(route('admin-booking.user-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]))
        ->assertNotFound();
    }

    public function testAdminCanEditBookingForUser()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 2);

        $payload = ['slots' => 4];
        $response = $this->put(route('admin-booking.edit-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]), $payload)
        ->assertRedirect(route('admin-booking.user', $this->user->getId()));

        $this->assertTrue(
            $this->user->fresh()->getCurrentNumberOfSlots() === 4
            && $this->appointment->fresh()->getSlotsTaken() === 4
            && $booking->fresh()->getSlotsTaken() === 4
        );
    }

    public function testSlotValidationForEditBooking()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 2);

        // slots < 0
        $payload = ['slots' => -1];
        $this->put(route('admin-booking.edit-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]), $payload)
        ->assertRedirect();

        $updatedBooking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        $this->assertTrue($booking->is($updatedBooking));

        // slots is a decimal
        $payload = ['slots' => 3.14];
        $this->put(route('admin-booking.edit-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]), $payload)
        ->assertRedirect();

        $updatedBooking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        $this->assertTrue($booking->is($updatedBooking));

        // slots is a string
        $payload = ['slots' => 'S T R I N G'];
        $this->put(route('admin-booking.edit-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]), $payload)
        ->assertRedirect();

        $updatedBooking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        $this->assertTrue($booking->is($updatedBooking));
    }

    public function testEditBookingBreachesMaxSlotsPerUserForOrganization()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 3);
        $payload = ['slots' => 4];

        $this->put(route('admin-booking.edit-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]), $payload)
        ->assertRedirect();

        $updatedBooking = AppointmentUser::fromUserIdAndAppointmentId($this->user->getId(), $this->appointment->getId());
        $this->assertTrue($booking->is($updatedBooking));
    }

    public function testBookingCancelsWhenEditingSlotsToZero()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 3);
        $payload = ['slots' => 0];

        $this->put(route('admin-booking.edit-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]), $payload)
        ->assertRedirect(route('admin-booking.user', $this->user->getId()));

        $this->assertTrue(
            $this->user->fresh()->getCurrentNumberOfSlots() === 0
            && $this->appointment->fresh()->getSlotsTaken() === 0
            && $booking->fresh() === null
        );
    }

    /* ========== CANCEL BOOKING ========== */

    public function testBookingCanBeCanceled()
    {
        $booking = $this->setUpBooking($this->user, $this->appointment, 3);
        $this->post(route('admin-booking.cancel-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]))
        ->assertRedirect(route('admin-booking.user', $this->user->getId()));

        $this->assertTrue(
            $this->user->fresh()->getCurrentNumberOfSlots() === 0
            && $this->appointment->fresh()->getSlotsTaken() === 0
            && $booking->fresh() === null
        );
    }

    public function testCannotCancelBookingThatDoesNotExist()
    {
        $this->post(route('admin-booking.cancel-booking', [
            'userId' => $this->user->getId(),
            'appointmentId' => $this->appointment->getId()
        ]))
        ->assertNotFound();
    }
}
