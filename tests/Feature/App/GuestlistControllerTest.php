<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @see GuestlistController
 */
class GuestlistControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testTrueIsTrue()
    {
        $this->assertTrue(true);
    }
}
