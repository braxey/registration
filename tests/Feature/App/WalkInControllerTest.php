<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\WalkIn;

/**
 * @see WalkInController
 */
class WalkInControllerTest extends TestCase {

    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /* ========== CREATE WALK-IN ========== */

    public function testAdminsCanViewCreateWalkInPage()
    {
        $this->actingAs($this->admin)
            ->get(route('walk-in.get-create'))
            ->assertSuccessful()
            ->assertViewIs('walk-ins.create');
    }

    public function testCreateWalkIn()
    {
        // without email or notes
        $payload = $this->walkInPayload();
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect(route('walk-in.show-waitlist'));
        $this->assertDatabaseHas('walk_ins', $payload);

        // without notes
        $payload = $this->walkInPayload(['email' => $this->faker->email]);
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect(route('walk-in.show-waitlist'));
        $this->assertDatabaseHas('walk_ins', $payload);

        // without email
        $payload = $this->walkInPayload(['notes' => $this->faker->text(100)]);
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect(route('walk-in.show-waitlist'));
        $this->assertDatabaseHas('walk_ins', $payload);
    }

    public function testRequiredFieldsWhenCreatingWalkIn()
    {
        // without name
        $payload = $this->walkInPayload(['name' => null]);
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseCount('walk_ins', 0);
        $response->assertSessionHasErrors('name');

        // without desired time
        $payload = $this->walkInPayload(['desired_time' => null]);
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseCount('walk_ins', 0);
        $response->assertSessionHasErrors('desired_time');

        // without number of slots
        $payload = $this->walkInPayload(['slots' => null]);
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseCount('walk_ins', 0);
        $response->assertSessionHasErrors('slots');
    }

    public function testSlotValidationWhenCreatingWalkIn()
    {
        // slots < 1
        $payload = $this->walkInPayload(['slots' => 0]);
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseCount('walk_ins', 0);
        $response->assertSessionHasErrors('slots');

        // slots is a decimal
        $payload = $this->walkInPayload(['slots' => 3.14]);
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseCount('walk_ins', 0);
        $response->assertSessionHasErrors('slots');

        // slots is a string
        $payload = $this->walkInPayload(['slots' => 'slot']);
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseCount('walk_ins', 0);
        $response->assertSessionHasErrors('slots');
    }

    public function testEmailMustBeValidIfPassedInWhenCreatingWalkIn()
    {
        $payload = $this->walkInPayload(['email' => 'invalid email']);
        $response = $this->actingAs($this->admin)->post(route('walk-in.create'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseCount('walk_ins', 0);
        $response->assertSessionHasErrors('email');
    }

    /* ========== UPDATE WALK-IN ========== */

    public function testAdminsCanViewEditWalkInPage()
    {
        $walkIn = WalkIn::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('walk-in.get-edit', $walkIn->getId()))
            ->assertSuccessful()
            ->assertViewIs('walk-ins.edit');
    }

    public function testEditWalkIn()
    {
        // without email or notes
        $walkIn = WalkIn::factory()->create();
        $data = $walkIn->jsonSerialize();

        $payload = array_merge($data, ['email' => null, 'notes' => null]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect(route('walk-in.get-edit', $walkIn->getId()));
        $this->assertDatabaseHas('walk_ins', array_merge($payload, ['email' => '']));

        // without notes
        $walkIn = WalkIn::factory()->create();
        $data = $walkIn->jsonSerialize();

        $payload = array_merge($data, ['notes' => null]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect(route('walk-in.get-edit', $walkIn->getId()));
        $this->assertDatabaseHas('walk_ins', $payload);

        // without email
        $walkIn = WalkIn::factory()->create();
        $data = $walkIn->jsonSerialize();

        $payload = array_merge($data, ['email' => null]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect(route('walk-in.get-edit', $walkIn->getId()));
        $this->assertDatabaseHas('walk_ins', array_merge($payload, ['email' => '']));
    }

    public function testRequiredFieldsWhenEditingWalkIn()
    {
        $walkIn = WalkIn::factory()->create();
        $data = $walkIn->jsonSerialize();

        // without name
        $payload = array_merge($data, ['name' => null]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('walk_ins', $data);
        $response->assertSessionHasErrors('name');

        // without desired time
        $payload = array_merge($data, ['desired_time' => null]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('walk_ins', $data);
        $response->assertSessionHasErrors('desired_time');

        // without number of slots
        $payload = array_merge($data, ['slots' => null]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('walk_ins', $data);
        $response->assertSessionHasErrors('slots');
    }

    public function testSlotValidationWhenEditingWalkIn()
    {
        $walkIn = WalkIn::factory()->create();
        $data = $walkIn->jsonSerialize();

        // slots < 1
        $payload = array_merge($data, ['slots' => 0]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('walk_ins', $data);
        $response->assertSessionHasErrors('slots');

        // slots is a decimal
        $payload = array_merge($data, ['slots' => 3.14]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('walk_ins', $data);
        $response->assertSessionHasErrors('slots');

        // slots is a string
        $payload = array_merge($data, ['slots' => 'a string']);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('walk_ins', $data);
        $response->assertSessionHasErrors('slots');
    }

    public function testEmailMustBeValidIfPassedInWhenEditingWalkIn()
    {
        $walkIn = WalkIn::factory()->create();
        $data = $walkIn->jsonSerialize();

        $payload = array_merge($data, ['email' => 'invalid email']);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('walk_ins', $data);
        $response->assertSessionHasErrors('email');
    }

    public function testEditingWalkInSlotsAffectsAppointmentIfLinked()
    {
        $appointment = Appointment::factory()->withTotalSlots(15)->withSlotsTaken(5)->create();
        $appointmentData = $appointment->jsonSerialize();
        $walkIn = WalkIn::factory()->withSlots(5)->withAppointment($appointment)->create();
        $walkInData = $walkIn->jsonSerialize();

        // doesn't breach total slots
        $payload = array_merge($walkInData, ['slots' => 10]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $this->assertDatabaseHas('walk_ins', $payload);
        $this->assertDatabaseHas('appointments', array_merge($appointmentData, ['slots_taken' => 10]));

        // breaches total slots
        $payload = array_merge($walkInData, ['slots' => 20]);
        $response = $this->actingAs($this->admin)->put(route('walk-in.edit', $walkIn->getId()), $payload);
        $this->assertDatabaseHas('walk_ins', $payload);
        $this->assertDatabaseHas('appointments', array_merge($appointmentData, [
            'slots_taken' => 20,
            'total_slots' => 20
        ]));
    }

    /* ========== DELETE WALK-IN ========== */

    public function testAdminCanDeleteWalkIn()
    {
        $walkIn = WalkIn::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('walk-in.delete', $walkIn->getId()));
        $response->assertRedirect(route('walk-in.show-waitlist'));

        $this->assertDatabaseMissing('walk_ins', [
            'id' => $walkIn->getId(),
        ]);
    }

    public function testDeletingWalkInReturnsToLinkedAppointment()
    {
        $appointment = Appointment::factory()->withTotalSlots(15)->withSlotsTaken(5)->create();
        $appointmentData = $appointment->jsonSerialize();
        $walkIn = WalkIn::factory()->withSlots(5)->withAppointment($appointment)->create();

        $response = $this->actingAs($this->admin)->post(route('walk-in.delete', $walkIn->getId()));
        $this->assertDatabaseMissing('walk_ins', [
            'id' => $walkIn->getId(),
        ]);
        $this->assertDatabaseHas('appointments', array_merge($appointmentData, ['slots_taken' => 0]));
    }

    /* ========== HELPER FUNCTION ========== */

    private function walkInPayload(array $overrides = []): array
    {
        $valid = [
            'name' => $this->faker->name,
            'desired_time'  => now('EST')->format('Y-m-d H:i:s'),
            'slots' => 5,
        ];

        return array_merge($valid, $overrides);
    }
}
