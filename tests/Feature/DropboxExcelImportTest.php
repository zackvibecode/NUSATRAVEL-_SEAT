<?php

namespace Tests\Feature;

use App\Models\Departure;
use App\Models\HermesSeatActivity;
use App\Models\ImportRun;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropboxExcelImportTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-import-token-seatweb';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.import.token' => $this->token]);
    }

    public function test_rejects_missing_token(): void
    {
        $this->postJson('/api/imports/dropbox-excel', [
            'packages' => [
                ['name' => 'TRANSJAVA', 'destination' => 'INDONESIA'],
            ],
        ])->assertUnauthorized();
    }

    public function test_imports_package_departure_and_registrations(): void
    {
        $payload = $this->samplePayload();

        $response = $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $payload);

        $response->assertOk()
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('counts.packages_created', 1)
            ->assertJsonPath('counts.departures_created', 1)
            ->assertJsonPath('counts.registrations_created', 2);

        $this->assertDatabaseHas('packages', [
            'name' => 'TRANSJAVA',
            'destination' => 'INDONESIA',
        ]);

        $package = Package::where('name', 'TRANSJAVA')->firstOrFail();
        $departure = Departure::where('package_id', $package->id)
            ->whereDate('departure_date', '2026-09-15')
            ->firstOrFail();

        $this->assertSame(25, $departure->total_seats);
        $this->assertSame(3, $departure->registered_pax);
        $this->assertDatabaseCount('registrations', 2);
        $this->assertDatabaseCount('import_runs', 1);
    }

    public function test_second_import_updates_without_duplicates(): void
    {
        $payload = $this->samplePayload();

        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $payload)
            ->assertOk();

        $payload['packages'][0]['description'] = 'Updated from Dropbox';
        $payload['departures'][0]['total_seats'] = 30;
        $payload['registrations'][0]['pax'] = 3;

        $response = $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $payload);

        $response->assertOk()
            ->assertJsonPath('counts.packages_created', 0)
            ->assertJsonPath('counts.packages_updated', 1)
            ->assertJsonPath('counts.departures_created', 0)
            ->assertJsonPath('counts.departures_updated', 1)
            ->assertJsonPath('counts.registrations_created', 0)
            ->assertJsonPath('counts.registrations_updated', 2);

        $this->assertDatabaseCount('packages', 1);
        $this->assertDatabaseCount('departures', 1);
        $this->assertDatabaseCount('registrations', 2);

        $package = Package::where('name', 'TRANSJAVA')->firstOrFail();
        $this->assertSame('Updated from Dropbox', $package->description);

        $departure = Departure::firstOrFail();
        $this->assertSame(30, $departure->total_seats);
        $this->assertSame(4, $departure->registered_pax); // 3 + 1
    }

    public function test_dry_run_does_not_persist_domain_rows(): void
    {
        $payload = $this->samplePayload();

        $response = $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel?dry_run=1', $payload);

        $response->assertOk()
            ->assertJsonPath('dry_run', true)
            ->assertJsonPath('counts.packages_created', 1)
            ->assertJsonPath('counts.registrations_created', 2);

        $this->assertDatabaseCount('packages', 0);
        $this->assertDatabaseCount('departures', 0);
        $this->assertDatabaseCount('registrations', 0);
        $this->assertDatabaseCount('import_runs', 1);
        $this->assertTrue(ImportRun::firstOrFail()->dry_run);
        $this->assertDatabaseCount('hermes_seat_activities', 0);
    }

    public function test_skips_over_capacity_registration(): void
    {
        $payload = $this->samplePayload();
        $payload['departures'][0]['total_seats'] = 2;
        $payload['registrations'] = [
            [
                'name' => 'Big Group',
                'phone' => '011-0000000',
                'pax' => 5,
                'package_name' => 'TRANSJAVA',
                'destination' => 'INDONESIA',
                'departure_date' => '2026-09-15',
            ],
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $payload);

        $response->assertOk()
            ->assertJsonPath('counts.packages_created', 1)
            ->assertJsonPath('counts.departures_created', 1)
            ->assertJsonPath('counts.registrations_created', 0)
            ->assertJsonPath('counts.skipped', 1);

        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_total_seats_increase_records_positive_activity_with_travel_date(): void
    {
        $payload = $this->capacityOnlyPayload(25);

        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $payload)
            ->assertOk();

        HermesSeatActivity::query()->delete();

        $payload['departures'][0]['total_seats'] = 30;

        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $payload)
            ->assertOk();

        $this->assertDatabaseCount('hermes_seat_activities', 1);
        $activity = HermesSeatActivity::firstOrFail();
        $this->assertSame('TRANSJAVA', $activity->package_name);
        $this->assertTrue($activity->departure_date->isSameDay('2026-09-15'));
        $this->assertSame(5, $activity->seat_delta);
    }

    public function test_unchanged_seats_do_not_record_activity(): void
    {
        $payload = $this->capacityOnlyPayload(25);

        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $payload)
            ->assertOk();

        HermesSeatActivity::query()->delete();

        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $payload)
            ->assertOk();

        $this->assertDatabaseCount('hermes_seat_activities', 0);
    }

    public function test_pax_increase_records_negative_seat_activity(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $this->capacityOnlyPayload(25))
            ->assertOk();

        HermesSeatActivity::query()->delete();

        $payload = $this->capacityOnlyPayload(25);
        $payload['registrations'] = [
            [
                'name' => 'Ahmad Ali',
                'phone' => '012-3456789',
                'pax' => 3,
                'need_partner' => false,
                'package_name' => 'TRANSJAVA',
                'destination' => 'INDONESIA',
                'departure_date' => '2026-09-15',
            ],
        ];

        $this->withToken($this->token)
            ->postJson('/api/imports/dropbox-excel', $payload)
            ->assertOk();

        $this->assertDatabaseCount('hermes_seat_activities', 1);
        $activity = HermesSeatActivity::firstOrFail();
        $this->assertSame('TRANSJAVA', $activity->package_name);
        $this->assertTrue($activity->departure_date->isSameDay('2026-09-15'));
        $this->assertSame(-3, $activity->seat_delta);
    }

    public function test_dashboard_shows_hermes_activity_using_travel_date(): void
    {
        $user = User::factory()->create();

        HermesSeatActivity::create([
            'package_name' => 'Makassar',
            'departure_date' => '2026-08-20',
            'seat_delta' => 5,
        ]);
        $activity = HermesSeatActivity::firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hermes Update Activity')
            ->assertSee('Makassar')
            ->assertSee('Seat +5')
            ->assertSee('20 Aug 2026')
            ->assertSee('updated '.$activity->updated_at_label);
    }

    public function test_hermes_update_page_shows_activity_from_sidebar_route(): void
    {
        $user = User::factory()->create();

        HermesSeatActivity::create([
            'package_name' => 'Yunnan',
            'departure_date' => '2026-08-25',
            'seat_delta' => -3,
        ]);
        $activity = HermesSeatActivity::firstOrFail();

        $this->actingAs($user)
            ->get(route('hermes.updates'))
            ->assertOk()
            ->assertSee('Hermes Update')
            ->assertSee('Yunnan')
            ->assertSee('Seat -3')
            ->assertSee('25 Aug 2026');
    }

    /**
     * @return array<string, mixed>
     */
    private function capacityOnlyPayload(int $totalSeats): array
    {
        $payload = $this->samplePayload();
        $payload['departures'][0]['total_seats'] = $totalSeats;
        $payload['registrations'] = [];

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function samplePayload(): array
    {
        return json_decode(
            file_get_contents(base_path('docs/samples/dropbox-excel-import.sample.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
    }
}
