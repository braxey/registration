<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\WalkIn;

/**
 * @see WalkInMiddleware
 */
class WalkInMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private WalkIn $walkIn;

    protected function setUp(): void
    {
        parent::setUp();
        session(['walk-in-dry-run' => true]);
        $this->admin = User::factory()->admin()->create();
        $this->walkIn = WalkIn::factory()->create();
    }

    public function testCannotPassIfWalkInNotFound()
    {
        $this->actingAs($this->admin)
            ->get(route('walk-in.get-edit', $this->walkIn->getId() + 1))
            ->assertNotFound()
            ->assertSee('walk-in not found');
    }

    public function testCanPass()
    {
        $this->actingAs($this->admin)
            ->get(route('walk-in.get-edit', $this->walkIn->getId()))
            ->assertStatus(202)
            ->assertSee('passes walk-in middleware');
    }
}
