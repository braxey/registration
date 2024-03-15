<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->addAppointments();
    }

    /* ========== INDEX ========== */

    public function testIndexWithAdminLoggedIn()
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('appointments.index'));
        $response->assertOk();
        $response->assertViewHas('appointments');

        $appointments = $response->viewData('appointments');
        $this->assertCount(9, $appointments);
        $this->assertTrue($appointments[0]->getParsedStartTime() < $appointments[2]->getParsedStartTime());
        $this->assertTrue($appointments[3]->getParsedStartTime() < $appointments[5]->getParsedStartTime());
        $this->assertTrue($appointments[6]->getParsedStartTime() > $appointments[8]->getParsedStartTime());

        $this->assertTrue($appointments[0]->isInProgress());
        $this->assertTrue($appointments[3]->isUpcoming());
        $this->assertTrue($appointments[6]->isCompleted());
    }

    public function testIndexWithNonAdminLoggedIn()
    {
        $user = User::factory()->create();

        // no upcoming appointments are walk-in-only
        $response = $this->actingAs($user)->get(route('appointments.index'));
        $response->assertOk();
        $response->assertViewHas('appointments');

        $appointments = $response->viewData('appointments');
        $this->assertCount(3, $appointments);
        $this->assertTrue($appointments->first()->getParsedStartTime() < $appointments->last()->getParsedStartTime());

        $this->assertTrue($appointments->first()->isUpcoming());
        $this->assertTrue($appointments->last()->isUpcoming());

        // one upcoming appointment is walk-in-only
        $appointments->last()->setWalkInOnly(true);

        $response = $this->actingAs($user)->get(route('appointments.index'));
        $response->assertOk();
        $response->assertViewHas('appointments');

        $appointments = $response->viewData('appointments');
        $this->assertCount(2, $appointments);
        $this->assertTrue($appointments->first()->getParsedStartTime() < $appointments->last()->getParsedStartTime());

        $this->assertTrue($appointments->first()->isUpcoming());
        $this->assertTrue($appointments->last()->isUpcoming());
    }

    public function testIndexWithoutUserLoggedIn()
    {
        $response = $this->get(route('appointments.index'));
        $response->assertOk();

        $response->assertViewHas('appointments');
        $response->assertViewHas('user', null);

        $appointments = $response->viewData('appointments');
        $this->assertCount(3, $appointments);
        $this->assertTrue($appointments->first()->getParsedStartTime() < $appointments->last()->getParsedStartTime());

        $this->assertTrue($appointments->first()->isUpcoming());
        $this->assertTrue($appointments->last()->isUpcoming());
    }

    /* ========== USER DASHBOARD ========== */
    // testUserCanReachTheirDashboard (assert successful. set up bookings to assert)

    private function addAppointments()
    {
        for ($i = 0; $i < 3; $i++) {
            Appointment::factory()->create([
                'status' => 'upcoming',
                'start_time' => now('EST')->addDays(2)->addMinutes($i),
                'end_time' => now('EST')->addDays(2)->addHours(1)
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            Appointment::factory()->create([
                'status' => 'completed',
                'start_time' => now('EST')->subDays(3)->addMinutes($i),
                'end_time' => now('EST')->subDays(3)->addHours(1),
                'past_end' => true
            ]);
        }
        
        for ($i = 0; $i < 3; $i++) {
            Appointment::factory()->create([
                'status' => 'in progress',
                'start_time' => now('EST')->subMinutes($i),
                'end_time' => now('EST')->addHours(1)
            ]);
        }
    }
}
