<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;

/**
 * @see OrganizationController
 */
class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::find(1);
        $this->organization->openRegistration();
        $this->admin = User::factory()->admin()->create();
    }

    /* ========== GET EDIT ORGANIZATION PAGE ========== */

    public function testAdminCanGetOrganizationEditPage()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('organization.get-edit', $this->organization->getId()))
            ->assertSuccessful()
            ->assertViewHas('organization');
        $this->assertTrue($response->viewData('organization')->is($this->organization));
    }

    public function testFailureToGetEditPageIfOrganizationDoesNotExist()
    {
        $this->actingAs($this->admin)
            ->get(route('organization.get-edit', $this->organization->getId() + 1))
            ->assertNotFound();
    }

    /* ========== EDIT ORGANIZATION ========== */

    public function testAdminCanUpdateOrganizationName()
    {
        $newName = $this->faker->name;
        $payload = [
            'org_name' => $newName,
            'max_slots_per_user' => $this->organization->getMaxSlotsPerUser(),
        ];

        $this->actingAs($this->admin)
            ->put(route('organization.update', $this->organization->getId()), $payload)
            ->assertRedirect(route('organization.get-edit', $this->organization->getId()));

        $organization = Organization::find($this->organization->getId());
        $this->assertTrue(
            $organization->getName() !== $this->organization->getName()
            && $organization->getName() === $newName
        );
    }

    public function testAdminCanUpdateOrganizationMaxSlotsPerUser()
    {
        $payload = [
            'org_name'           => $this->organization->getName(),
            'max_slots_per_user' => 777,
        ];

        $this->actingAs($this->admin)
            ->put(route('organization.update', $this->organization->getId()), $payload)
            ->assertRedirect(route('organization.get-edit', $this->organization->getId()));

        $organization = Organization::find($this->organization->getId());
        $this->assertTrue(
            $organization->getMaxSlotsPerUser() !== $this->organization->getMaxSlotsPerUser()
            && $organization->getMaxSlotsPerUser() === 777
        );
    }

    public function testFailureToUpdateIfOrganizationDoesNotExist()
    {
        $payload = [
            'org_name' => $this->faker->name,
            'max_slots_per_user' => 777,
        ];

        $this->actingAs($this->admin)
            ->put(route('organization.update', $this->organization->getId() + 1), $payload)
            ->assertNotFound();
    }

    public function testNameValidationForOrganization()
    {
        // name note passed in
        $payload = ['max_slots_per_user' => 777];
        $this->actingAs($this->admin)
            ->put(route('organization.update', $this->organization->getId()), $payload)
            ->assertRedirect();
        $organization = Organization::find($this->organization->getId());
        $this->assertTrue($organization->is($this->organization));

        // name is passed in, but empty
        $payload = array_merge($payload, ['org_name' => '']);
        $this->actingAs($this->admin)
            ->put(route('organization.update', $this->organization->getId()), $payload)
            ->assertRedirect();
        $organization = Organization::find($this->organization->getId());
        $this->assertTrue($organization->is($this->organization));
    }

    public function testMaxSlotsPerUserValidationForOrganization()
    {
        // slots < 1
        $payload = ['slots' => 0, 'org_name' => $this->faker->name];
        $this->actingAs($this->admin)
            ->put(route('organization.update', $this->organization->getId()), $payload)
            ->assertRedirect();
        $organization = Organization::find($this->organization->getId());
        $this->assertTrue($organization->getMaxSlotsPerUser() === 6);

        // slots is a decimal
        $payload = array_merge($payload, ['slots' => 3.14]);
        $this->actingAs($this->admin)
            ->put(route('organization.update', $this->organization->getId()), $payload)
            ->assertRedirect();
        $organization = Organization::find($this->organization->getId());
        $this->assertTrue($organization->getMaxSlotsPerUser() === 6);

        // slots is a string
        $payload = array_merge($payload, ['slots' => 'HARVEY']);
        $this->actingAs($this->admin)
            ->put(route('organization.update', $this->organization->getId()), $payload)
            ->assertRedirect();
        $organization = Organization::find($this->organization->getId());
        $this->assertTrue($organization->getMaxSlotsPerUser() === 6);
    }

    /* ========== OPEN/CLOSE REGISTRATION ========== */

    public function testAdminCanOpenAndCloseRegistration()
    {
        $this->actingAs($this->admin)
            ->post(route('organization.toggle-registration', $this->organization->getId()))
            ->assertRedirect(route('organization.get-edit', $this->organization->getId()));
        $organization = Organization::find($this->organization->getId());
        $this->assertTrue($organization->registrationIsClosed());

        $this->actingAs($this->admin)
            ->post(route('organization.toggle-registration', $this->organization->getId()))
            ->assertRedirect(route('organization.get-edit', $this->organization->getId()));
        $organization = Organization::find($this->organization->getId());
        $this->assertTrue($organization->registrationIsOpen());
    }

    public function testFailureToToggleRegistrationIfOrganizationDoesNotExist()
    {
        $this->actingAs($this->admin)
            ->post(route('organization.toggle-registration', $this->organization->getId() + 1))
            ->assertNotFound();
    }
}
