<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\WalkIn;

class WalkInMiddlewareTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        session(['dry-run' => true]);
        $this->admin = User::factory()->admin()->create();
    }

    // testCannotPassIfWalkInNotFound
    // testCanPass
}
