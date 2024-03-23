<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;

class AppointmentMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();
        session(['appointment-dry-run' => true]);
        $this->admin = User::factory()->admin()->create();
        $this->appointment = Appointment::factory()->create();
    }

    public function testCannotPassIfAppointmentIsNotFound()
    {
        $this->actingAs($this->admin)
            ->get(route('appointment.get-edit', $this->appointment->getId() + 1))
            ->assertNotFound()
            ->assertSee('appointment not found');
    }

    public function testCanPassIfAppointmentIsFound()
    {
        $this->actingAs($this->admin)
            ->get(route('appointment.get-edit', $this->appointment->getId()))
            ->assertStatus(202)
            ->assertSee('passes appointment middleware');
    }
}
