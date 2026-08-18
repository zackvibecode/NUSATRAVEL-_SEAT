<?php

namespace Tests\Feature;

use App\Models\Departure;
use App\Models\HermesSeatActivity;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripCapacityTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-capacity-token';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.import.token' => $this->token]);
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    /**
     * @return array<string, mixed>
     */
    private function importPayload(int $totalSeats, array $registrations = []): array
    {
        return [
            'filename' => 'capacity_test.xlsx',
            'packages' => [
                ['name' => 'BIGTRIP', 'destination' => 'INDONESIA'],
            ],
            'departures' => [
                [
                    'package_name' => 'BIGTRIP',
                    'destination' => 'INDONESIA',
                    'departure_date' => '2026-10-01',
                    'return_date' => '2026-10-05',
                    'total_seats' => $totalSeats,
                    'airline' => 'AirAsia',
                    'status' => 'open',
                ],
            ],
            'registrations' => $registrations,
        ];
    }

    public function test_capacity_30_is_not_clamped_to_20(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $this->importPayload(30))
            ->assertOk();

        $departure = Departure::firstOrFail();
        $this->assertSame(30, $departure->total_seats);
        $this->assertSame(30, $departure->available_seats);
    }

    public function test_capacity_35_is_not_clamped_to_20(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $this->importPayload(35))
            ->assertOk();

        $this->assertSame(35, Departure::firstOrFail()->total_seats);
    }

    public function test_capacity_50_is_not_clamped_to_20(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $this->importPayload(50))
            ->assertOk();

        $this->assertSame(50, Departure::firstOrFail()->total_seats);
    }

    public function test_available_seats_use_real_capacity_not_fixed_20(): void
    {
        // 22 pax registered on a 30-seat trip: with the old bug this
        // would have been impossible (20 cap) — now 8 seats remain.
        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $this->importPayload(30, [
                [
                    'name' => 'Big Family',
                    'phone' => '012-1111111',
                    'pax' => 22,
                    'package_name' => 'BIGTRIP',
                    'destination' => 'INDONESIA',
                    'departure_date' => '2026-10-01',
                ],
            ]))
            ->assertOk()
            ->assertJsonPath('counts.registrations_created', 1);

        $departure = Departure::withSum('registrations as registered_pax_sum', 'pax')->firstOrFail();
        $this->assertSame(30, $departure->total_seats);
        $this->assertSame(22, $departure->registered_pax);
        $this->assertSame(8, $departure->available_seats);
    }

    public function test_capacity_over_20_displays_on_trips_page(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $this->importPayload(35))
            ->assertOk();

        $response = $this->actingAs($this->user)->get(route('departures.index'));

        $response->assertOk()
            ->assertSee('/ 35 booked')
            ->assertSee('35 left');
    }

    public function test_capacity_over_20_shows_on_departure_detail(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $this->importPayload(50))
            ->assertOk();

        $departure = Departure::firstOrFail();

        $response = $this->actingAs($this->user)->get(route('departures.show', $departure));

        $response->assertOk()
            ->assertSee('50</p>', false);
    }

    public function test_registration_above_20_pax_succeeds_on_30_seat_trip(): void
    {
        $package = Package::create(['name' => 'BIGTRIP', 'destination' => 'INDONESIA', 'status' => 'active']);
        $departure = Departure::create([
            'package_id' => $package->id,
            'departure_date' => now()->addMonth(),
            'return_date' => now()->addMonth()->addDays(4),
            'total_seats' => 30,
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->user)->post(route('registrations.store'), [
            'departure_id' => $departure->id,
            'name' => 'Mega Group',
            'pax' => 25,
        ]);

        $response->assertRedirect(route('departures.show', $departure));

        $departure->refresh();
        $this->assertSame(25, $departure->registered_pax);
        $this->assertSame(5, $departure->available_seats);
    }

    public function test_overbooking_still_blocked_beyond_real_capacity(): void
    {
        $package = Package::create(['name' => 'BIGTRIP', 'destination' => 'INDONESIA', 'status' => 'active']);
        $departure = Departure::create([
            'package_id' => $package->id,
            'departure_date' => now()->addMonth(),
            'return_date' => now()->addMonth()->addDays(4),
            'total_seats' => 30,
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->user)->post(route('registrations.store'), [
            'departure_id' => $departure->id,
            'name' => 'Too Big Group',
            'pax' => 31,
        ]);

        $response->assertSessionHasErrors('pax');
        $this->assertSame(0, $departure->registered_pax);
    }

    public function test_hermes_departure_api_returns_real_capacity(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $this->importPayload(30))
            ->assertOk();

        $response = $this->withToken($this->token)->getJson('/api/hermes/departures');

        $response->assertOk()
            ->assertJsonPath('data.0.total_seats', 30)
            ->assertJsonPath('data.0.available_seats', 30);
    }

    public function test_dashboard_available_seats_sums_real_capacity(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $this->importPayload(30, [
                [
                    'name' => 'Family',
                    'phone' => '012-1111111',
                    'pax' => 10,
                    'package_name' => 'BIGTRIP',
                    'destination' => 'INDONESIA',
                    'departure_date' => '2026-10-01',
                ],
            ]))
            ->assertOk();

        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk()->assertSee('20');
    }
}
