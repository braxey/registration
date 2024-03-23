<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        session(['user-dry-run' => true]);
        $this->actingAs($this->admin = User::Factory()->admin()->create());
        $this->user = User::factory()->create();
    }

    public function testCannotPassIfUserIsNotFound()
    {
        $this->get(route('admin-booking.user', $this->user->getId() + 1))
            ->assertNotFound()
            ->assertSee('user not found');
    }

    public function testCanPassIfUserIsFound()
    {
        $this->get(route('admin-booking.user', $this->user->getId()))
            ->assertStatus(202)
            ->assertSee('passes user middleware');
    }
}
