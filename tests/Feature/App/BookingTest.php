<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Organization;
use App\Models\AppointmentUser;

class BookingTest extends TestCase {

    use DatabaseTransactions;

    /* ========== BOOK ========== */

    public function testUserMustHaveVerifiedEmailToAccessBookingPage(){
        // Create a user with an unverified email
        $user = User::factory()->unverified()->create();

        // Authenticate as the user
        $this->actingAs($user);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Make a GET request to the booking page
        $response = $this->get(route('appointment.book', $appointment->id));

        // Assert that the response redirects to the email verification notice page
        $response->assertRedirect(route('verification.notice'));
    }

    public function testUserWithVerifiedEmailCanAccessBookingPage(){
        // Create an organization
        $org = Organization::factory()->create();

        // Create a user with a verified email
        $user = User::factory()->create(['email_verified_at' => now()]);

        // Authenticate as the user
        $this->actingAs($user);

        // Create an appointment
        $appointment = Appointment::factory()->create();

        // Make a GET request to the booking page
        $response = $this->get(route('appointment.book', $appointment->id));

        // Assert that the response is successful (status code 200)
        $response->assertStatus(200);
    }

    



    
}
