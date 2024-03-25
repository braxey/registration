<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\Organization;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->addAppointments();
        Organization::find(1)->setMaxSlotsPerUser(100);
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

    public function testUserCanSeeDashboardWithNoAppointments()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function testUserCanSeeDashboardWithNoUpcomingAppointments()
    {
        $user = User::factory()->create();
        $allPastAppointments = Appointment::where('status', 'completed')->get();
        
        // set up completed appointments
        $allPastAppointments->each(function (Appointment $appointment) use ($user) {
            $this->setUpBooking($user, $appointment, 2);
        });

        $response = $this->actingAs($user)->get(route('dashboard'))
                                ->assertOk()
                                ->assertViewHas('allAppointments')->assertViewHas('upcomingAppointments', null)->assertViewHas('pastAppointments');

        $allAppointments = $response->viewData('allAppointments');
        $pastAppointments = $response->viewData('pastAppointments');

        $this->assertCount($allPastAppointments->count(), $pastAppointments); // make sure all bookings are present
        $this->assertEquals($allAppointments, $pastAppointments); // past appointments should be the only appointments
        $sortedPastAppointments = $pastAppointments->sortByDesc('start_time');
        $this->assertEquals($pastAppointments, $sortedPastAppointments); // past appointments should already be from most recent to oldest
    }

    public function testUserCanSeeDashboardWithNoCompletedAppointments()
    {
        $user = User::factory()->create();
        $allUncompletedAppointments = Appointment::whereNot('status', 'completed')->get();
        
        // set up uncompleted appointments
        $allUncompletedAppointments->each(function (Appointment $appointment) use ($user) {
            $this->setUpBooking($user, $appointment, 2);
        });

        $response = $this->actingAs($user)->get(route('dashboard'))
                                ->assertOk()
                                ->assertViewHas('allAppointments')->assertViewHas('upcomingAppointments')->assertViewHas('pastAppointments', null);

        $allAppointments = $response->viewData('allAppointments');
        $upcomingAppointments = $response->viewData('upcomingAppointments');

        $this->assertCount($allUncompletedAppointments->count(), $upcomingAppointments); // make sure all bookings are present
        $this->assertEquals($allAppointments, $upcomingAppointments); // upcoming appointments should be the only appointments
        $sortedUpcomingAppointments = $upcomingAppointments->sortBy('start_time');
        $this->assertEquals($upcomingAppointments, $sortedUpcomingAppointments); // upcoming appointments should already be from oldest to most recent
    }

    public function testUserCanSeeDashboardWithUpcomingAndCompletedAppointments()
    {
        $user = User::factory()->create();
        $allExistingAppointments = Appointment::all();
        
        // set up uncompleted appointments
        $allExistingAppointments->each(function (Appointment $appointment) use ($user) {
            $this->setUpBooking($user, $appointment, 2);
        });

        $response = $this->actingAs($user)->get(route('dashboard'))
                                ->assertOk()
                                ->assertViewHas('allAppointments')->assertViewHas('upcomingAppointments')->assertViewHas('pastAppointments');

        $allAppointments = $response->viewData('allAppointments');
        $upcomingAppointments = $response->viewData('upcomingAppointments');
        $pastAppointments = $response->viewData('pastAppointments');

        // make sure all bookings are present
        $this->assertCount($allExistingAppointments->count(), $allAppointments);
        $this->assertCount($allExistingAppointments->count(), $upcomingAppointments->concat($pastAppointments));

        $this->assertEquals($allAppointments, $upcomingAppointments->concat($pastAppointments));
    }
}
