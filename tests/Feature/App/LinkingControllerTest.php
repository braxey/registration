<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Appointment;
use App\Models\WalkIn;

class LinkingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Collection $appointments;
    protected WalkIn $walkIn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->appointments = Appointment::factory(15)->create();
        $this->walkIn = WalkIn::factory()->withSlots(5)->create();
    }

    public function testAdminCanGetLinkAppointmentPage()
    {
        $response = $this->actingAs($this->admin)
                         ->get(route('walk-in.get-link-appointment', $this->walkIn->getId()))
                         ->assertSuccessful();

        $response->assertViewHas(['walkIn', 'nonCompletedAppointments']);
        $seenAppointments = $response->viewData('nonCompletedAppointments');
        $this->assertCount(15, $seenAppointments);
        $this->assertTrue($seenAppointments->first()->getParsedStartTime() < $seenAppointments->last()->getParsedStartTime());
    }

    /* ========== LINKING ========== */

    public function testWalkInWithNoLinkCanBeLinked()
    {
        $appointment = $this->appointments->first();
        $this->link($this->walkIn->getId(), $appointment->getId())
            ->assertRedirect(route('walk-in.show-waitlist'));

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $updatedWalkIn = WalkIn::fromId($this->walkIn->getId());

        $this->assertTrue($updatedAppointment->getSlotsTaken() === 5);
        $this->assertTrue($updatedWalkIn->getAppointment()->is($updatedAppointment));
    }

    public function testNoChangeIfWalkInTriesLinkingToSameAppointment()
    {
        $appointment = $this->appointments->first();

        // first pass
        $this->link($this->walkIn->getId(), $appointment->getId());

        $updatedAppointmentData = Appointment::fromId($appointment->getId())->jsonSerialize();
        $updatedWalkInData = WalkIn::fromId($this->walkIn->getId())->jsonSerialize();

        // second pass
        $this->link($this->walkIn->getId(), $appointment->getId());

        $this->assertDatabaseHas('walk_ins', $updatedWalkInData);
        $this->assertDatabaseHas('appointments', $updatedAppointmentData);
    }

    public function testWalkInWithLinkGetsLinkedToDifferentAppointment()
    {
        $appointmentOne = $this->appointments->first();
        $appointmentTwo = $this->appointments->last();

        $this->link($this->walkIn->getId(), $appointmentOne->getId());
        $this->link($this->walkIn->getId(), $appointmentTwo->getId());

        $updatedAppointmentOne = Appointment::fromId($appointmentOne->getId());
        $updatedAppointmentTwo = Appointment::fromId($appointmentTwo->getId());
        $updatedWalkIn = WalkIn::fromId($this->walkIn->getId());

        $this->assertTrue($updatedAppointmentOne->getSlotsTaken() === 0);
        $this->assertTrue($updatedAppointmentTwo->getSlotsTaken() === $this->walkIn->getNumberOfSlots());
        $this->assertTrue($updatedWalkIn->getAppointment()->is($updatedAppointmentTwo));
    }

    /* ========== UNLINKING ========== */

    public function testWalkInCanBeUnlinked()
    {
        $appointment = $this->appointments->first();
        $this->link($this->walkIn->getId(), $appointment->getId());
        $this->unlink($this->walkIn->getId(), $appointment->getId());

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $updatedWalkIn = WalkIn::fromId($this->walkIn->getId());

        $this->assertTrue($updatedAppointment->getSlotsTaken() === 0);
        $this->assertNull($updatedWalkIn->getAppointment());
    }

    public function testUnlinkingWhenPassedInAppointmentsDoNotMatch()
    {
        $appointment = $this->appointments->first();
        $this->link($this->walkIn->getId(), $appointment->getId());
        $this->unlink($this->walkIn->getId(), $appointment->getId() + 1)
            ->assertBadRequest();

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $updatedWalkIn = WalkIn::fromId($this->walkIn->getId());

        $this->assertTrue($updatedAppointment->getSlotsTaken() === 5);
        $this->assertTrue($updatedWalkIn->getAppointment()->is($updatedAppointment));
    }

    public function testUnlinkingWhenWalkInIsNotLinked()
    {
        $appointment = $this->appointments->first();
        $this->unlink($this->walkIn->getId(), $appointment->getId() + 1)
            ->assertBadRequest();

        $updatedAppointment = Appointment::fromId($appointment->getId());
        $updatedWalkIn = WalkIn::fromId($this->walkIn->getId());

        $this->assertTrue($updatedAppointment->getSlotsTaken() === 0);
        $this->assertNull($updatedWalkIn->getAppointment());
    }

    /* ========== HELPER FUNCTIONS ========== */

    private function link(int $walkInId, int $appointmentId)
    {
        return $this->actingAs($this->admin)->post(route('walk-in.link-appointment', [
            'walkInId'      => $walkInId,
            'appointmentId' => $appointmentId
        ]));
    }

    private function unlink(int $walkInId, int $appointmentId)
    {
        return $this->actingAs($this->admin)->post(route('walk-in.unlink-appointment', [
            'walkInId'      => $walkInId,
            'appointmentId' => $appointmentId
        ]));
    }
}
