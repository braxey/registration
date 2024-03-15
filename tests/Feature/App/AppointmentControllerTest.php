<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;

class AppointmentControllerTest extends TestCase {

    use RefreshDatabase;

    protected User $nonAdmin;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->nonAdmin = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
    }

    /* ========== CREATE APPOINTMENT ========== */

    public function testNonAdminsCantViewCreateAppointmentPage()
    {
        $response = $this->actingAs($this->nonAdmin)->get(route('appointment.get-create'));
        $response->assertUnauthorized();
    }

    public function testNonAdminsCantCreateAppointment()
    {
        $payload = $this->appointmentPayload();
        $response = $this->actingAs($this->nonAdmin)->post(route('appointment.create'), $payload);
        $response->assertUnauthorized();
        $this->assertDatabaseCount('appointments', 0);
    }

    public function testAdminsCanViewCreateAppointmentPage()
    {
        $response = $this->actingAs($this->admin)->get(route('appointment.get-create'));
        $response->assertOk();
        $response->assertViewIs('appointments.create');
    }

    public function testCreateAppointment()
    {
        $payload = $this->appointmentPayload();
        $expected = $this->expectedAppointmentPayload([
            'start_time' => $payload['start_time']
        ]);
        $response = $this->actingAs($this->admin)->post(route('appointment.create'), $payload);
        $response->assertRedirect(route('appointments.index'));
        $this->assertDatabaseHas('appointments', $expected);
    }

    public function testDescriptionIsRequiredWhenCreatingAppointment()
    {
        $payload = $this->appointmentPayload([
            'description' => ''
        ]);
        $response = $this->actingAs($this->admin)->post(route('appointment.create'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseCount('appointments', 0);
        $response->assertSessionHasErrors('description');
    }

    public function testStartTimeFieldValidationFailsWhenEmptyWhenCreatingAppointment()
    {
        $payload = $this->appointmentPayload([
            'start_time' => ''
        ]);
        $response = $this->actingAs($this->admin)->post(route('appointment.create'), $payload);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('start_time');
    }

    public function testTotalSlotsCannotBeLessThanOneWhenCreatingAppointment()
    {
        $payload = $this->appointmentPayload([
            'total_slots' => 0
        ]);
        $response = $this->actingAs($this->admin)->post(route('appointment.create'), $payload);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('total_slots');
    }

    public function testTotalSlotsCannotBeDecimalsWhenCreatingAppointment()
    {
        $payload = $this->appointmentPayload([
            'total_slots' => 3.14
        ]);
        $response = $this->actingAs($this->admin)->post(route('appointment.create'), $payload);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('total_slots');
    }

    public function testTotalSlotsCannotBeStringsWhenCreatingAppointment()
    {
        $payload = $this->appointmentPayload([
            'total_slots' => '<script>console.log("hello")</script>'
        ]);
        $response = $this->actingAs($this->admin)->post(route('appointment.create'), $payload);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('total_slots');
    }

    /* ========== UPDATE APPOINTMENT ========== */

    public function testNonAdminsCantViewEditAppointmentPage(){
        $appointment = Appointment::factory()->create();
        $response = $this->actingAs($this->nonAdmin)->get(route('appointment.get-edit', $appointment->getId()));
        $response->assertUnauthorized();
    }

    public function testNonAdminsCantUpdateAppointment()
    {
        $appointment = Appointment::factory()->create();
        $payload = $this->appointmentPayload();
        $response = $this->actingAs($this->nonAdmin)->put(route('appointment.update', $appointment->getId()), $payload);
        $response->assertUnauthorized();

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $this->assertEquals($appointment->getDescription(), $updatedAppointment->getDescription());
        $this->assertEquals($appointment->getParsedStartTime()->format('Y-m-d H:i:s'), $updatedAppointment->getParsedStartTime()->format('Y-m-d H:i:s'));
        $this->assertEquals($appointment->getParsedEndTime()->format('Y-m-d H:i:s'), $updatedAppointment->getParsedEndTime()->format('Y-m-d H:i:s'));
        $this->assertEquals($appointment->getTotalSlots(), $updatedAppointment->getTotalSlots());
    }

    public function testAdminsCanViewEditAppointmentPage()
    {
        $appointment = Appointment::factory()->create();
        $response = $this->actingAs($this->admin)->get(route('appointment.get-edit', $appointment->getId()));
        $response->assertOk();
        $response->assertViewHas('appointment');

        $seenAppointment = $response->viewData('appointment');
        $this->assertEquals($appointment->getDescription(), $seenAppointment->getDescription());
        $this->assertEquals($appointment->getParsedStartTime()->format('Y-m-d H:i:s'), $seenAppointment->getParsedStartTime()->format('Y-m-d H:i:s'));
        $this->assertEquals($appointment->getParsedEndTime()->format('Y-m-d H:i:s'), $seenAppointment->getParsedEndTime()->format('Y-m-d H:i:s'));
        $this->assertEquals($appointment->getTotalSlots(), $seenAppointment->getTotalSlots());
    }

    public function testUpdateAppointment()
    {
        $appointment = Appointment::factory()->create();
        $payload = $this->appointmentPayload();
        $expected = $this->expectedAppointmentPayload([
            'start_time' => $payload['start_time']
        ]);
        $response = $this->actingAs($this->admin)->put(route('appointment.update', $appointment->getId()), $payload);
        $response->assertRedirect(route('appointment.get-edit', $appointment->getId()));
 
         $this->assertDatabaseHas('appointments', array_merge($expected, [
             'id' => $appointment->getId()
         ]));
    }

    public function testDescriptionIsRequiredWhenUpdatingAppointment()
    {
        $appointment = Appointment::factory()->create();
        $payload = $this->appointmentPayload([
            'description' => ''
        ]);
        $response = $this->actingAs($this->admin)->put(route('appointment.update', $appointment->getId()), $payload);
        $response->assertStatus(302);

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $this->assertEquals($appointment->getDescription(), $updatedAppointment->getDescription());
        $response->assertSessionHasErrors('description');
    }

    public function testStartTimeFieldValidationFailsWhenEmptyWhenUpdatingAppointment()
    {
        $appointment = Appointment::factory()->create();
        $payload = $this->appointmentPayload([
            'start_time' => ''
        ]);
        $response = $this->actingAs($this->admin)->put(route('appointment.update', $appointment->getId()), $payload);
        $response->assertStatus(302);

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $this->assertEquals($appointment->getParsedStartTime()->format('Y-m-d H:i:s'), $updatedAppointment->getParsedStartTime()->format('Y-m-d H:i:s'));
        $response->assertSessionHasErrors('start_time');
    }


    public function testTotalSlotsCannotBeLessThanZeroWhenUpdatingAppointment(){
        $appointment = Appointment::factory()->create();
        $payload = $this->appointmentPayload([
            'total_slots' => -1
        ]);
        $response = $this->actingAs($this->admin)->put(route('appointment.update', $appointment->getId()), $payload);
        $response->assertStatus(302);

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $this->assertEquals($appointment->getTotalSlots(), $updatedAppointment->getTotalSlots());
        $response->assertSessionHasErrors('total_slots');
    }


    public function testTotalSlotsCannotBeDecimalWhenUpdatingAppointment()
    {
        $appointment = Appointment::factory()->create();
        $payload = $this->appointmentPayload([
            'total_slots' => 3.14
        ]);
        $response = $this->actingAs($this->admin)->put(route('appointment.update', $appointment->getId()), $payload);
        $response->assertStatus(302);

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $this->assertEquals($appointment->getTotalSlots(), $updatedAppointment->getTotalSlots());
        $response->assertSessionHasErrors('total_slots');
    }

    public function testTotalSlotsCannotBeStringWhenUpdatingAppointment(){
        $appointment = Appointment::factory()->create();
        $payload = $this->appointmentPayload([
            'total_slots' => 'string'
        ]);
        $response = $this->actingAs($this->admin)->put(route('appointment.update', $appointment->getId()), $payload);
        $response->assertStatus(302);

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $this->assertEquals($appointment->getTotalSlots(), $updatedAppointment->getTotalSlots());
        $response->assertSessionHasErrors('total_slots');
    }

    /* ========== DELETE APPOINTMENT ========== */

    public function testNonAdminsCannotDeleteAppointments()
    {
        $appointment = Appointment::factory()->create();
        $response = $this->actingAs($this->nonAdmin)->post(route('appointment.delete', $appointment->getId()));
        $response->assertUnauthorized();
        $this->assertDatabaseHas('appointments', ['id' => $appointment->getId()]);
    }

    public function testAdminCanDeleteAppointmentAndReallocateSlots()
    {
        $appointment = Appointment::factory()->create();

        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        AppointmentUser::insertBooking($firstUser->getId(), $appointment->getId(), 2);
        $firstUser->incrementSlotsBooked(2);

        AppointmentUser::insertBooking($secondUser->getId(), $appointment->getId(), 3);
        $secondUser->incrementSlotsBooked(3);

        $response = $this->actingAs($this->admin)->post(route('appointment.delete', $appointment->getId()));
        $response->assertRedirect(route('appointments.index'));

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->getId(),
        ]);

        $this->assertDatabaseMissing('appointment_user', [
            'appointment_id' => $appointment->getId(),
            'user_id'        => $firstUser->getId(),
        ]);
        $this->assertDatabaseMissing('appointment_user', [
            'appointment_id' => $appointment->getId(),
            'user_id'        => $secondUser->getId(),
        ]);

        $this->assertEquals(0, $firstUser->fresh()->getCurrentNumberOfSlots());
        $this->assertEquals(0, $secondUser->fresh()->getCurrentNumberOfSlots());
    }

    public function testAdminCanDeleteAppointmentWithoutReallocatingSlotsIfPastEnd()
    {
        $appointment = Appointment::factory()->create(['past_end' => true]);

        AppointmentUser::insertBooking($this->nonAdmin->getId(), $appointment->getId(), 2);
        $this->nonAdmin->incrementSlotsBooked(2);

        $response = $this->actingAs($this->admin)->post(route('appointment.delete', $appointment->getId()));
        $response->assertRedirect(route('appointments.index'));

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->getId(),
        ]);

        $this->assertDatabaseMissing('appointment_user', [
            'appointment_id' => $appointment->getId(),
            'user_id'        => $this->nonAdmin->getId(),
        ]);

        $this->assertEquals(2, $this->nonAdmin->fresh()->getCurrentNumberOfSlots());
    }

    private function appointmentPayload(array $overrides = []): array
    {
        $startTime = now('EST');
        $valid = [
            'description' => 'Test Appointment',
            'start_time'  => $startTime->format('Y-m-d H:i:s'),
            'end_time'    => $startTime->addHour()->format('Y-m-d H:i:s'),
            'total_slots' => 5,
        ];

        return array_merge($valid, $overrides);
    }

    private function expectedAppointmentPayload(array $overrides = []): array
    {
        $expected = [
            'description' => 'Test Appointment',
            'start_time'  => now('EST')->format('Y-m-d H:i:s'),
            'total_slots' => 5,
        ];

        return array_merge($expected, $overrides);
    }
}
