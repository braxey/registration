<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;

class AppointmentsTest extends TestCase {

    use DatabaseTransactions;

    /* ========== INDEX ========== */

    public function testIndexWithUserLoggedIn(){
        // Create a test user
        $user = User::factory()->create();

        // Create some test appointments in the database with different statuses and start times
        $this->addAppointments();
    
        // Perform the GET request to the index method with an authenticated user
        $response = $this->actingAs($user)->get(route('appointments.index'));

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the appointments are displayed correctly in the view
        $response->assertViewHas('appointments');

        // Get the appointments from the response
        $appointments = $response->viewData('appointments');

        // Assert that the appointments are sorted correctly by time and status
        $this->assertCount(9, $appointments);
        $this->assertTrue($appointments[0]->start_time < $appointments[2]->start_time);
        $this->assertTrue($appointments[3]->start_time < $appointments[5]->start_time);
        $this->assertTrue($appointments[6]->start_time > $appointments[8]->start_time);

        // Assert that the appointments are sorted correctly by status priority
        $this->assertEquals('in progress', $appointments[0]->status);
        $this->assertEquals('upcoming', $appointments[3]->status);
        $this->assertEquals('completed', $appointments[6]->status);
    }


    public function testIndexWithoutUserLoggedIn(){
        // Create some test appointments in the database with different statuses and start times
        $this->addAppointments();

        // Perform the GET request to the index method without an authenticated user
        $response = $this->get(route('appointments.index'));

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the appointments are displayed correctly in the view
        $response->assertViewHas('appointments');
        $response->assertViewHas('user', null);

        // Get the appointments from the response
        $appointments = $response->viewData('appointments');

        // Assert that the appointments are sorted correctly by time and status
        $this->assertCount(9, $appointments);
        $this->assertTrue($appointments[0]->start_time < $appointments[2]->start_time);
        $this->assertTrue($appointments[3]->start_time < $appointments[5]->start_time);
        $this->assertTrue($appointments[6]->start_time > $appointments[8]->start_time);

        // Assert that the appointments are sorted correctly by status priority
        $this->assertEquals('in progress', $appointments[0]->status);
        $this->assertEquals('upcoming', $appointments[3]->status);
        $this->assertEquals('completed', $appointments[6]->status);
    }

    /* ========== CREATE APPOINTMENT ========== */

    public function testNonAdminsCantViewCreateAppointmentForm(){
        // Create an admin user for testing
        $notAdmin = User::factory()->create();

        // Authenticate as the admin user
        $this->actingAs($notAdmin);

        // Send a GET request to the create appointment route
        $response = $this->get(route('appointment.get-create'));

        // Assert that the response status is 404 Not Found
        $response->assertNotFound();
    }

    public function testNonAdminsCantCreateAppointment(){
        // Create an admin user for testing
        $notAdmin = User::factory()->create();

        // Authenticate as the admin user
        $this->actingAs($notAdmin);

        // Generate the form data for creating an appointment
        $formData = [
            'description' => 'Test Appointment',
            'start_time' => now('EST')->format('Y-m-d H:i:s'),
            'end_time' => now('EST')->addHour()->format('Y-m-d H:i:s'),
            'total_slots' => 5,
        ];

        // Send a POST request to the create appointment route
        $response = $this->post(route('appointment.create'), $formData);

        // Assert that the non-admin user is redirected to a 404 page
        $response->assertNotFound();

        // Assert that the appointment does not exist in the database
        $this->assertDatabaseMissing('appointments', $formData);
    }

    public function testAdminsCanViewCreateAppointmentForm(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Send a GET request to the create appointment route
        $response = $this->get(route('appointment.get-create'));

        // Assert that the response status is 200 OK
        $response->assertOk();

        // Assert that the create appointment view is returned
        $response->assertViewIs('appointments.create');

    }

    public function testCreateAppointment(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Generate the form data for creating an appointment
        $formData = [
            'description' => 'Test Appointment',
            'start_time' => now('EST')->format('Y-m-d H:i:s'),
            'end_time' => now('EST')->addHour()->format('Y-m-d H:i:s'),
            'total_slots' => 5,
        ];

        // Send a POST request to the create appointment route
        $response = $this->post(route('appointment.create'), $formData);

        // Assert that the appointment was successfully created
        $response->assertRedirect(route('appointments.index'));

        // Assert that the appointment exists in the database
        $this->assertDatabaseHas('appointments', $formData);
    }

    public function testDescriptionIsRequiredWhenCreatingAppointment(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Send a POST request to create an appointment with an empty description
        $response = $this->post(route('appointment.create'), [
            'description' => '',
            'start_time' => now('EST'),
            'end_time' => now('EST')->addHour(),
            'total_slots' => 10,
        ]);

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment was not created
        $this->assertDatabaseCount('appointments', 0);

        // Assert that an error message is present
        $response->assertSessionHasErrors('description');
    }

    public function testStartTimeFieldValidationFailsWhenEmptyWhenCreatingAppointment(): void{
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->post(route('appointment.create'), [
                'description' => 'Appointment 1',
                'start_time' => '',
                'end_time' => '2023-06-15 11:00:00',
                'total_slots' => 5,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('start_time');
    }

    public function testEndTimeFieldValidationFailsWhenEmptyWhenCreatingAppointment(): void{
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->post(route('appointment.create'), [
                'description' => 'Appointment 1',
                'start_time' => '2023-06-15 11:00:00',
                'end_time' => '',
                'total_slots' => 5,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('end_time');
    }

    public function testStartTimeFieldValidationFailsWhenNotBeforeEndTimeWhenCreatingAppointment(): void{
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->post(route('appointment.create'), [
                'description' => 'Appointment 1',
                'start_time' => '2023-06-15 14:00:00',
                'end_time' => '2023-06-15 13:00:00',
                'total_slots' => 5,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    public function testTotalSlotsCannotBeLessThanOneWhenCreatingAppointment(): void{
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->post(route('appointment.create'), [
                'description' => 'Appointment 1',
                'start_time' => '2023-06-15 14:00:00',
                'end_time' => '2023-06-15 13:00:00',
                'total_slots' => 0,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('total_slots');
    }

    public function testTotalSlotsCannotBeDecimalsWhenCreatingAppointment(): void{
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->post(route('appointment.create'), [
                'description' => 'Appointment 1',
                'start_time' => '2023-06-15 14:00:00',
                'end_time' => '2023-06-15 13:00:00',
                'total_slots' => 3.14,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('total_slots');
    }

    public function testTotalSlotsCannotBeStringsWhenCreatingAppointment(): void{
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->post(route('appointment.create'), [
                'description' => 'Appointment 1',
                'start_time' => '2023-06-15 14:00:00',
                'end_time' => '2023-06-15 13:00:00',
                'total_slots' => '<script>console.log("hello")</script>',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('total_slots');
    }

    /* ========== UPDATE APPOINTMENT ========== */

    public function testNonAdminsCantViewEditAppointmentForm(){
        // Create a non-admin user
        $user = User::factory()->create();

        // Authenticate as the non-admin user
        $this->actingAs($user);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Make a request to view the edit form for the appointment
        $response = $this->get(route('appointment.get-edit', $appointment->id));

        // Assert that the user is redirected to the appointments.index route
        $response->assertRedirect(route('appointments.index'));
    }

    public function testNonAdminsCantUpdateAppointment(){
        // Create a non-admin user
        $user = User::factory()->create();

        // Authenticate as the non-admin user
        $this->actingAs($user);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Set the start_time and end_time values
        $startTime = now('EST')->addHour();
        $endTime = now('EST')->addHour(2);

        // Make a request to update the appointment
        $response = $this->put(route('appointment.update', $appointment->id), [
            'description' => 'Updated Description',
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'total_slots' => 10,
        ]);

        // Assert that the non-admin user is redirected to the appointments.index route
        $response->assertNotFound();

        // Retrieve the updated appointment from the database
        $updatedAppointment = Appointment::find($appointment->id);

        // Assert that the appointment details were not updated
        $this->assertEquals($appointment->description, $updatedAppointment->description);
        $this->assertEquals($appointment->start_time->format('Y-m-d H:i:s'), $updatedAppointment->start_time);
        $this->assertEquals($appointment->end_time->format('Y-m-d H:i:s'), $updatedAppointment->end_time);
        $this->assertEquals($appointment->total_slots, $updatedAppointment->total_slots);
    }

    public function testAdminsCanViewEditAppointmentForm(){
        // Create an admin user
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin
        $this->actingAs($admin);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Make a request to view the edit form for the appointment
        $response = $this->get(route('appointment.get-edit', $appointment->id));

        // Assert that the response is successful
        $response->assertStatus(200);

        // Assert that the response contains the appointment details
        $response->assertSee($appointment->description);
        $response->assertSee($appointment->start_time->format('Y-m-d H:i:s'));
        $response->assertSee($appointment->end_time->format('Y-m-d H:i:s'));
        $response->assertSee((string) $appointment->total_slots);
    }

    public function testUpdateAppointment(){
         // Create an admin user
         $admin = User::factory()->admin()->create();

         // Authenticate as the admin
         $this->actingAs($admin);
 
         // Create an appointment
         $appointment = Appointment::factory()->create();
 
         // Make a request to edit the appointment
         $response = $this->put(route('appointment.update', $appointment->id), [
             'description' => 'Updated appointment description',
             'start_time' => '2023-06-12 10:00:00',
             'end_time' => '2023-06-12 11:00:00',
             'total_slots' => 10,
         ]);
 
         // Assert that the response is successful
         $response->assertStatus(302);
 
         // Assert that the appointment details have been updated
         $this->assertDatabaseHas('appointments', [
             'id' => $appointment->id,
             'description' => 'Updated appointment description',
             'start_time' => '2023-06-12 10:00:00',
             'end_time' => '2023-06-12 11:00:00',
             'total_slots' => 10,
         ]);
    }

    public function testDescriptionIsRequiredWhenUpdatingAppointment(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Send a PUT request to update the appointment with an empty description
        $response = $this->put(route('appointment.update', $appointment->id), [
            'description' => '',
            'start_time' => now('EST'),
            'end_time' => now('EST')->addHour(),
            'total_slots' => 10,
        ]);

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment description was not updated
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals($appointment->description, $updatedAppointment->description);

        // Assert that an error message is present for the description field
        $response->assertSessionHasErrors('description');
    }

    public function testStartTimeFieldValidationFailsWhenEmptyWhenUpdatingAppointment(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Send a PUT request to update the appointment with an empty start_time
        $response = $this->put(route('appointment.update', $appointment->id), [
            'description' => 'Updated Description',
            'start_time' => '',
            'end_time' => now('EST')->addHour(),
            'total_slots' => 10,
        ]);

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment start_time was not updated
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals($appointment->start_time->format('Y-m-d H:i:s'), $updatedAppointment->start_time);

        // Assert that an error message is present for the start_time field
        $response->assertSessionHasErrors('start_time');
    }

    public function testEndTimeFieldValidationFailsWhenEmptyWhenUpdatingAppointment(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Send a PUT request to update the appointment with an empty start_time
        $response = $this->put(route('appointment.update', $appointment->id), [
            'description' => 'Updated Description',
            'start_time' => now('EST')->addHour(),
            'end_time' => '',
            'total_slots' => 10,
        ]);

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment start_time was not updated
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals($appointment->end_time->format('Y-m-d H:i:s'), $updatedAppointment->end_time);

        // Assert that an error message is present for the start_time field
        $response->assertSessionHasErrors('end_time');
    }

    public function testStartTimeFieldValidationFailsWhenNotBeforeEndTimeWhenUpdatingAppointment(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Send a PUT request to update the appointment with an invalid start_time
        $response = $this->put(route('appointment.update', $appointment->id), [
            'description' => 'Updated Description',
            'start_time' => now('EST')->addHour(), // Set start_time after end_time
            'end_time' => now('EST'),
            'total_slots' => 10,
        ]);

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment start_time was not updated
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals($appointment->start_time->format('Y-m-d H:i:s'), $updatedAppointment->start_time);

        // Assert that an error message is present for the start_time field
        $response->assertSessionHasErrors();
    }


    public function testTotalSlotsCannotBeLessThanZeroWhenUpdatingAppointment(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Send a PUT request to update the appointment with a negative total_slots value
        $response = $this->put(route('appointment.update', $appointment->id), [
            'description' => 'Updated Description',
            'start_time' => now('EST'),
            'end_time' => now('EST')->addHour(),
            'total_slots' => -1, // Set total_slots to a negative value
        ]);

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment total_slots was not updated
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals($appointment->total_slots, $updatedAppointment->total_slots);

        // Assert that an error message is present for the total_slots field
        $response->assertSessionHasErrors('total_slots');
    }


    public function testTotalSlotsCannotBeDecimalWhenUpdatingAppointment(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Send a PUT request to update the appointment with a negative total_slots value
        $response = $this->put(route('appointment.update', $appointment->id), [
            'description' => 'Updated Description',
            'start_time' => now('EST'),
            'end_time' => now('EST')->addHour(),
            'total_slots' => 3.14, // Set total_slots to a negative value
        ]);

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment total_slots was not updated
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals($appointment->total_slots, $updatedAppointment->total_slots);

        // Assert that an error message is present for the total_slots field
        $response->assertSessionHasErrors('total_slots');
    }

    public function testTotalSlotsCannotBeStringWhenUpdatingAppointment(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Send a PUT request to update the appointment with a negative total_slots value
        $response = $this->put(route('appointment.update', $appointment->id), [
            'description' => 'Updated Description',
            'start_time' => now('EST'),
            'end_time' => now('EST')->addHour(),
            'total_slots' => 'string', // Set total_slots to a negative value
        ]);

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment total_slots was not updated
        $updatedAppointment = Appointment::find($appointment->id);
        $this->assertEquals($appointment->total_slots, $updatedAppointment->total_slots);

        // Assert that an error message is present for the total_slots field
        $response->assertSessionHasErrors('total_slots');
    }

    /* ========== DELETE APPOINTMENT ========== */

    public function testNonAdminsCannotDeleteAppointments(){
        // Create a non-admin user for testing
        $user = User::factory()->create();

        // Authenticate as the non-admin user
        $this->actingAs($user);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Send a POST request to delete the appointment
        $response = $this->post(route('appointment.delete', $appointment->id));

        // Assert that the response status is a redirect
        $response->assertStatus(404);

        // Assert that the appointment was not deleted
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id]);
    }

    public function testAdminCanDeleteAppointmentAndReallocateSlots(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Create an appointment
        $appointment = Appointment::factory()->create(['past_end' => false]);

        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Book slots for the appointment
        AppointmentUser::create([
            'appointment_id' => $appointment->id,
            'user_id' => $user1->id,
            'slots_taken' => 2,
        ]);
        $user1->slots_booked = 2;
        $user1->save();

        AppointmentUser::create([
            'appointment_id' => $appointment->id,
            'user_id' => $user2->id,
            'slots_taken' => 3,
        ]);
        $user2->slots_booked = 3;
        $user2->save();

        // Send a POST request to delete the appointment
        $response = $this->post(route('appointment.delete', $appointment->id));

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment was deleted
        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);

        // Assert that the user-appointment entries were deleted
        $this->assertDatabaseMissing('appointment_user', [
            'appointment_id' => $appointment->id,
            'user_id' => $user1->id,
        ]);
        $this->assertDatabaseMissing('appointment_user', [
            'appointment_id' => $appointment->id,
            'user_id' => $user2->id,
        ]);

        // Assert that the users' slots were updated
        $this->assertEquals(0, $user1->fresh()->slots_booked);
        $this->assertEquals(0, $user2->fresh()->slots_booked);
    }

    public function testAdminCanDeleteAppointmentWithoutReallocatingSlotsIfPastEnd(){
        // Create an admin user for testing
        $admin = User::factory()->admin()->create();

        // Authenticate as the admin user
        $this->actingAs($admin);

        // Create a past_end appointment
        $appointment = Appointment::factory()->create(['past_end' => true]);

        // Create a user
        $user = User::factory()->create();

        // Book slots for the appointment
        AppointmentUser::create([
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
            'slots_taken' => 2,
        ]);
        $user->slots_booked = 2;
        $user->save();


        // Send a POST request to delete the appointment
        $response = $this->post(route('appointment.delete', $appointment->id));

        // Assert that the response status is a redirect
        $response->assertStatus(302);

        // Assert that the appointment was deleted
        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);

        // Assert that the user-appointment entry was deleted
        $this->assertDatabaseMissing('appointment_user', [
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
        ]);

        // Assert that the user's slots were not updated
        $this->assertEquals(2, $user->fresh()->slots_booked);
    }


    private function addAppointments(){
        for($i = 0 ; $i < 3 ; $i++){
            $upcomingAppointments = Appointment::factory()->create([
                'status' => 'upcoming',
                'start_time' => now('EST')->addDays(2)->addMinutes($i),
                'end_time' => now('EST')->addDays(2)->addHours(1)
            ]);
        }

        for($i = 0 ; $i < 3 ; $i++){
            $completedAppointments = Appointment::factory()->create([
                'status' => 'completed',
                'start_time' => now('EST')->subDays(3)->addMinutes($i),
                'end_time' => now('EST')->subDays(3)->addHours(1),
                'past_end' => true
            ]);
        }
        
        for($i = 0 ; $i < 3 ; $i++){
            $inProgressAppointments = Appointment::factory()->create([
                'status' => 'in progress',
                'start_time' => now('EST')->subMinutes($i),
                'end_time' => now('EST')->addHours(1)
            ]);
        }
    }
}
