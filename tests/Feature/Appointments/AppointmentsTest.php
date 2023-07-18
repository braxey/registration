<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;

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

    

    private function addAppointments(){
        for($i = 0 ; $i < 3 ; $i++){
            $upcomingAppointments = Appointment::factory()->create([
                'status' => 'upcoming',
                'start_time' => now()->addDays(2)->addMinutes($i),
                'end_time' => now()->addDays(2)->addHours(1)
            ]);
        }

        for($i = 0 ; $i < 3 ; $i++){
            $completedAppointments = Appointment::factory()->create([
                'status' => 'completed',
                'start_time' => now()->subDays(3)->addMinutes($i),
                'end_time' => now()->subDays(3)->addHours(1),
                'past_end' => true
            ]);
        }
        
        for($i = 0 ; $i < 3 ; $i++){
            $inProgressAppointments = Appointment::factory()->create([
                'status' => 'in progress',
                'start_time' => now()->subMinutes($i),
                'end_time' => now()->addHours(1)
            ]);
        }
    }

}