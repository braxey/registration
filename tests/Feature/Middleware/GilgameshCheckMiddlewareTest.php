<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;

/**
 * @see GilgameshCheckMiddleware
 */
class GilgameshCheckMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $gilgamesh;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        session(['gilgamesh-check-dry-run' => true]);
        $this->gilgamesh = User::factory()->gilgamesh()->create();
        $this->admin = User::factory()->admin()->create();
    }

    public function testNonGilgameshAdminCannotPass()
    {
        $this->actingAs($this->admin)
            ->get(route('mass-mailer.landing'))
            ->assertUnauthorized()
            ->assertSee('you do not have access');
    }

    public function testGilgameshCanPass()
    {
        $this->actingAs($this->gilgamesh)
            ->get(route('mass-mailer.landing'))
            ->assertStatus(202)
            ->assertSee('passes gilgamesh check middleware');
    }
}
