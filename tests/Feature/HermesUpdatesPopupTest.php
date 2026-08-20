<?php

namespace Tests\Feature;

use App\Models\HermesSeatActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HermesUpdatesPopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_visit_shows_no_popup(): void
    {
        $user = User::factory()->create();

        // Activity exists, but user has never visited before — no popup expected.
        HermesSeatActivity::create([
            'departure_id' => null,
            'package_name' => 'Bali 4D3N',
            'departure_date' => now()->addMonth(),
            'seat_delta' => 2,
            'activity_type' => 'registration_created',
            'actor_name' => 'Ahmad Sufian',
            'activity_note' => 'Pax 2',
        ]);

        $response = $this->actingAs($user)->get(route('hermes.updates'));

        $response->assertOk()
            ->assertDontSee('whatsNewModal')
            ->assertSee('Hermes Update');
    }

    public function test_second_visit_shows_popup_with_new_customers(): void
    {
        $user = User::factory()->create();

        // First visit stamps the "seen at" timestamp.
        $this->actingAs($user)->get(route('hermes.updates'));

        // 5 minutes pass, then new activities arrive.
        $this->travel(5)->minutes();
        HermesSeatActivity::create([
            'package_name' => 'Bali 4D3N',
            'departure_date' => now()->addMonth(),
            'seat_delta' => 2,
            'activity_type' => 'registration_created',
            'actor_name' => 'Ahmad Sufian',
            'activity_note' => 'Pax 2',
        ]);

        HermesSeatActivity::create([
            'package_name' => 'Krabi 3D2N',
            'departure_date' => now()->addMonths(2),
            'seat_delta' => -1,
            'activity_type' => 'registration_deleted',
            'actor_name' => 'Siti Nurhaliza',
            'activity_note' => 'Cancelled',
        ]);

        HermesSeatActivity::create([
            'package_name' => 'Bali 4D3N',
            'departure_date' => now()->addMonth(),
            'seat_delta' => 1,
            'activity_type' => 'registration_updated',
            'actor_name' => 'Lim Wei Ming',
            'activity_note' => 'Pax 1->2',
        ]);

        // Trip-level activity should NOT appear in the popup.
        HermesSeatActivity::create([
            'package_name' => 'Hanoi 5D4N',
            'departure_date' => now()->addMonths(3),
            'seat_delta' => 5,
            'activity_type' => 'departure_created',
            'actor_name' => 'Hermes',
            'activity_note' => 'Trip baru',
        ]);

        $response = $this->actingAs($user)->get(route('hermes.updates'));

        $response->assertOk()
            ->assertSee('whatsNewModal')
            ->assertSee('Apa yang baharu')
            ->assertSee('Ahmad Sufian')
            ->assertSee('Siti Nurhaliza')
            ->assertSee('Lim Wei Ming');

        // Trip-level change appears in the main feed but NOT inside the customer popup.
        $content = $response->getContent();
        $start = strpos($content, 'id="whatsNewModal"');
        $end = strpos($content, 'Ok, tengok sudah');
        $this->assertNotFalse($start, 'Popup markup missing.');
        $this->assertNotFalse($end, 'Popup footer missing.');
        $popupHtml = substr($content, $start, $end - $start);
        $this->assertStringNotContainsString('Hanoi 5D4N', $popupHtml);
    }

    public function test_popup_hidden_again_when_no_new_activities_since_last_visit(): void
    {
        $user = User::factory()->create();

        // Activity created BEFORE the first visit.
        $this->travel(10)->minutes();
        HermesSeatActivity::create([
            'package_name' => 'Bali 4D3N',
            'departure_date' => now()->addMonth(),
            'seat_delta' => 2,
            'activity_type' => 'registration_created',
            'actor_name' => 'Ahmad Sufian',
        ]);

        $this->actingAs($user)->get(route('hermes.updates'));

        // Second visit with nothing new — no popup.
        $this->actingAs($user)
            ->get(route('hermes.updates'))
            ->assertOk()
            ->assertDontSee('whatsNewModal');
    }
}
