<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;

class AdminCheckMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $nonAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        session(['admin-check-dry-run' => true]);
        $this->admin = User::factory()->admin()->create();
        $this->nonAdmin = User::factory()->create();
    }

    public function testNonAdminsCannotPass()
    {
        $this->actingAs($this->nonAdmin)
            ->get(route('appointment.get-create'))
            ->assertUnauthorized()
            ->assertSee('not an admin');
    }

    public function testAdminsCanPass()
    {
        $this->actingAs($this->admin)
            ->get(route('appointment.get-create'))
            ->assertStatus(202)
            ->assertSee('passes admin check middleware');
    }
}
