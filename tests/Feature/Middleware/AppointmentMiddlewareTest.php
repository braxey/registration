<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;

class AppointmentMiddlewareTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        session(['dry-run' => true]);
        $this->admin = User::factory()->admin()->create();
    }

    // testCannotPassIfAppointmentIsNotFound
    // testCanPass
}
