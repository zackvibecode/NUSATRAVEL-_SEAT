<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HermesGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_hermes_guide_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('hermes.guide'))
            ->assertOk()
            ->assertSee('Hermes Sync')
            ->assertSee('Dropbox');
    }
}
