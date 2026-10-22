<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * This app has no admin list, so Pulse keeps its default rule: the
 * dashboard is only open in local development.
 */
class PulseAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pulse_is_closed_outside_local_development(): void
    {
        $this->actingAs(User::factory()->create())->get('/pulse')->assertForbidden();
        $this->get('/pulse')->assertForbidden();
    }
}
