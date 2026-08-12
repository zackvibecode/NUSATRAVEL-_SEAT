<?php

namespace Tests\Feature;

use App\Models\Departure;
use App\Models\ImportRun;
use App\Models\Package;
use App\Models\Registration;
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
