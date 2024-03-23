<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Appointment;
use App\Models\AppointmentUser;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Organization::find(1)->setMaxSlotsPerUser(100);
    }

    public function testUserAccountsCanBeDeleted(): void
    {
        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(DeleteUserForm::class)
            ->set('password', 'password')
            ->call('deleteUser');

        $this->assertNull($user->fresh());
    }

    public function testCorrectPasswordMustBeProvidedBeforeAccountCanBeDeleted(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(DeleteUserForm::class)
            ->set('password', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors(['password']);

        $this->assertNotNull($user->fresh());
    }

    public function testSlotsAreReturnedIfTheUserIsDeletedBeforeTheAppointmentStarts(): void
    {
        $this->actingAs($user = User::factory()->create());

        $upcomingAppointment = Appointment::factory()->create();
        $inProgressAppointment = Appointment::factory()->inProgress()->create();
        $completedAppointment = Appointment::factory()->pastEnd()->create();

        $upcomingBooking = $this->setUpBooking($user, $upcomingAppointment, 3);
        $inProgressBooking = $this->setUpBooking($user, $inProgressAppointment, 4);
        $completedBooking = $this->setUpBooking($user, $completedAppointment, 5);

        $component = Livewire::test(DeleteUserForm::class)
            ->set('password', 'password')
            ->call('deleteUser');

        // test bookings are gone
        $this->assertNull($upcomingBooking->fresh());
        $this->assertNull($inProgressBooking->fresh());
        $this->assertNull($completedBooking->fresh());

        // test only upcoming appointment was given slots back
        $this->assertSame(0, $upcomingAppointment->fresh()->getSlotsTaken());
        $this->assertSame(4, $inProgressAppointment->fresh()->getSlotsTaken());
        $this->assertSame(5, $completedAppointment->fresh()->getSlotsTaken());
    }
}
