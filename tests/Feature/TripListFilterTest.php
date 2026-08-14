<?php

namespace Tests\Feature;

use App\Models\Departure;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;
use App\Support\TripListFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripListFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_departures_can_be_filtered_by_destination(): void
    {
        $indonesia = $this->createDeparture('TRANSJAVA', 'INDONESIA', '2026-09-15');
        $this->createDeparture('GREATWALL', 'China', '2026-09-20');

        $response = $this->actingAs($this->user)
            ->get(route('departures.index', ['destination' => 'INDONESIA']));

        $response->assertOk()
            ->assertSee('TRANSJAVA')
            ->assertDontSee('GREATWALL');
    }

    public function test_departures_can_be_filtered_by_month_and_year(): void
    {
        $this->createDeparture('SEP-ONLY-TRIP', 'INDONESIA', '2026-09-15');
        $this->createDeparture('OCT-ONLY-TRIP', 'INDONESIA', '2026-10-15');

        $response = $this->actingAs($this->user)
            ->get(route('departures.index', ['month' => 9, 'year' => 2026]));

        $response->assertOk()
            ->assertSee('SEP-ONLY-TRIP');

        $this->assertStringNotContainsString(
            '<td class="px-6 py-4 font-bold">OCT-ONLY-TRIP</td>',
            $response->getContent(),
        );
    }

    public function test_departures_sort_by_date_asc_vs_desc(): void
    {
        $this->createDeparture('ZZZ-LATER-TRIP', 'INDONESIA', '2026-12-01');
        $this->createDeparture('AAA-EARLIER-TRIP', 'INDONESIA', '2026-09-01');

        $this->actingAs($this->user)
            ->get(route('departures.index', [
                'destination' => 'INDONESIA',
                'sort' => 'departure_date',
                'dir' => 'asc',
            ]))
            ->assertOk()
            ->assertSeeInOrder(['AAA-EARLIER-TRIP', 'ZZZ-LATER-TRIP']);

        $this->actingAs($this->user)
            ->get(route('departures.index', [
                'destination' => 'INDONESIA',
                'sort' => 'departure_date',
                'dir' => 'desc',
            ]))
            ->assertOk()
            ->assertSeeInOrder(['ZZZ-LATER-TRIP', 'AAA-EARLIER-TRIP']);
    }

    public function test_packages_can_be_filtered_by_destination(): void
    {
        Package::create(['name' => 'TRANSJAVA', 'destination' => 'INDONESIA', 'status' => 'active']);
        Package::create(['name' => 'GREATWALL', 'destination' => 'China', 'status' => 'active']);

        $response = $this->actingAs($this->user)
            ->get(route('packages.index', ['destination' => 'INDONESIA']));

        $response->assertOk()
            ->assertSee('TRANSJAVA')
            ->assertDontSee('GREATWALL');
    }

    public function test_participants_can_be_filtered_by_departure_month(): void
    {
        $sep = $this->createDeparture('SEP PKG', 'INDONESIA', '2026-09-10');
        $oct = $this->createDeparture('OCT PKG', 'INDONESIA', '2026-10-10');

        Registration::create([
            'departure_id' => $sep->id,
            'name' => 'Ali September',
            'phone' => '0123456789',
            'pax' => 2,
            'need_partner' => false,
        ]);

        Registration::create([
            'departure_id' => $oct->id,
            'name' => 'Siti October',
            'phone' => '0198765432',
            'pax' => 1,
            'need_partner' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('participants.index', ['month' => 9, 'year' => 2026]));

        $response->assertOk()
            ->assertSee('Ali September')
            ->assertDontSee('Siti October');
    }

    public function test_trip_list_filter_helper_applies_destination_to_departures(): void
    {
        $indonesiaPkg = Package::create(['name' => 'INDO', 'destination' => 'INDONESIA', 'status' => 'active']);
        $chinaPkg = Package::create(['name' => 'CHINA', 'destination' => 'China', 'status' => 'active']);

        Departure::create([
            'package_id' => $indonesiaPkg->id,
            'departure_date' => '2026-09-01',
            'return_date' => '2026-09-08',
            'total_seats' => 20,
            'status' => 'open',
        ]);

        Departure::create([
            'package_id' => $chinaPkg->id,
            'departure_date' => '2026-09-01',
            'return_date' => '2026-09-08',
            'total_seats' => 20,
            'status' => 'open',
        ]);

        $filter = new TripListFilter('departures', 'departures.index', ['destination' => 'INDONESIA']);

        $results = $filter->applyToDepartureQuery(Departure::query())->get();

        $this->assertCount(1, $results);
        $this->assertSame('INDO', $results->first()->package->name);
    }

    public function test_departures_can_be_searched_by_package_name(): void
    {
        $matched = $this->createDeparture('TRANSJAVA ULTIMATE', 'INDONESIA', '2026-09-15');
        $other = $this->createDeparture('GREATWALL TOUR', 'China', '2026-09-20');

        $response = $this->actingAs($this->user)
            ->get(route('departures.index', ['search' => 'transjava']));

        $response->assertOk()
            ->assertSee('TRANSJAVA ULTIMATE')
            ->assertSee(route('departures.show', $matched))
            ->assertDontSee(route('departures.show', $other));
    }

    public function test_departures_can_be_searched_by_customer_name(): void
    {
        $departure = $this->createDeparture('CUSTOMER TRIP', 'INDONESIA', '2026-09-15');
        $other = $this->createDeparture('OTHER TRIP', 'China', '2026-09-20');

        Registration::create([
            'departure_id' => $departure->id,
            'name' => 'Ahmad Bin Ali',
            'phone' => '0123456789',
            'pax' => 2,
            'need_partner' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('departures.index', ['search' => 'ahmad']));

        $response->assertOk()
            ->assertSee('CUSTOMER TRIP')
            ->assertSee(route('departures.show', $departure))
            ->assertDontSee(route('departures.show', $other));
    }

    public function test_departures_search_with_no_matches_shows_empty_state(): void
    {
        $departure = $this->createDeparture('REAL TRIP', 'INDONESIA', '2026-09-15');

        $response = $this->actingAs($this->user)
            ->get(route('departures.index', ['search' => 'nonexistent-xyz']));

        $response->assertOk()
            ->assertDontSee(route('departures.show', $departure))
            ->assertSee('No departures found');
    }

    public function test_departures_search_is_case_insensitive_and_partial(): void
    {
        $this->createDeparture('GreatWall Adventure', 'China', '2026-09-20');

        $response = $this->actingAs($this->user)
            ->get(route('departures.index', ['search' => 'WALL']));

        $response->assertOk()
            ->assertSee('GreatWall Adventure');
    }

    public function test_trip_list_filter_helper_applies_search_to_departures(): void
    {
        $indonesiaPkg = Package::create(['name' => 'INDO TOUR', 'destination' => 'INDONESIA', 'status' => 'active']);
        $chinaPkg = Package::create(['name' => 'CHINA TOUR', 'destination' => 'China', 'status' => 'active']);

        Departure::create([
            'package_id' => $indonesiaPkg->id,
            'departure_date' => '2026-09-01',
            'return_date' => '2026-09-08',
            'total_seats' => 20,
            'status' => 'open',
        ]);

        Departure::create([
            'package_id' => $chinaPkg->id,
            'departure_date' => '2026-09-01',
            'return_date' => '2026-09-08',
            'total_seats' => 20,
            'status' => 'open',
        ]);

        $filter = new TripListFilter('departures', 'departures.index', ['search' => 'indo']);

        $results = $filter->applyToDepartureQuery(Departure::query())->get();

        $this->assertCount(1, $results);
        $this->assertSame('INDO TOUR', $results->first()->package->name);
    }

    private function createDeparture(string $name, string $destination, string $date): Departure
    {
        $package = Package::create([
            'name' => $name,
            'destination' => $destination,
            'status' => 'active',
        ]);

        return Departure::create([
            'package_id' => $package->id,
            'departure_date' => $date,
            'return_date' => date('Y-m-d', strtotime($date.' +7 days')),
            'total_seats' => 30,
            'status' => 'open',
        ]);
    }
}
