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

    public function test_past_departures_are_hidden_by_default(): void
    {
        $past = $this->createDeparture('PAST TRIP', 'INDONESIA', now()->subDay()->toDateString());
        $future = $this->createDeparture('FUTURE TRIP', 'INDONESIA', now()->addDays(10)->toDateString());

        $response = $this->actingAs($this->user)->get(route('departures.index'));

        $response->assertOk()
            ->assertSee('FUTURE TRIP')
            ->assertDontSee(route('departures.show', $past))
            ->assertSee(route('departures.show', $future));

        $this->assertDatabaseHas('departures', ['id' => $past->id]);
    }

    public function test_past_departures_visible_when_include_past_is_on(): void
    {
        $past = $this->createDeparture('PAST TRIP', 'INDONESIA', now()->subDay()->toDateString());

        $response = $this->actingAs($this->user)
            ->get(route('departures.index', ['past' => '1']));

        $response->assertOk()
            ->assertSee('PAST TRIP')
            ->assertSee(route('departures.show', $past));
    }

    public function test_departures_default_sort_is_nearest_first(): void
    {
        $far = $this->createDeparture('FAR TRIP', 'INDONESIA', now()->addMonths(3)->toDateString());
        $near = $this->createDeparture('NEAR TRIP', 'INDONESIA', now()->addDays(5)->toDateString());
        $mid = $this->createDeparture('MID TRIP', 'INDONESIA', now()->addWeeks(3)->toDateString());

        $this->actingAs($this->user)
            ->get(route('departures.index'))
            ->assertOk()
            ->assertSeeInOrder(['NEAR TRIP', 'MID TRIP', 'FAR TRIP']);
    }

    public function test_packages_with_only_past_departures_are_hidden_by_default(): void
    {
        $pastOnly = Package::create(['name' => 'PAST ONLY PKG', 'destination' => 'INDONESIA', 'status' => 'active']);
        Departure::create([
            'package_id' => $pastOnly->id,
            'departure_date' => now()->subWeek()->toDateString(),
            'return_date' => now()->subDay()->toDateString(),
            'total_seats' => 20,
            'status' => 'open',
        ]);

        $this->actingAs($this->user)
            ->get(route('packages.index'))
            ->assertOk()
            ->assertDontSee('PAST ONLY PKG');

        $this->actingAs($this->user)
            ->get(route('packages.index', ['past' => '1']))
            ->assertOk()
            ->assertSee('PAST ONLY PKG');
    }

    public function test_packages_with_no_departures_stay_visible(): void
    {
        Package::create(['name' => 'BRAND NEW PKG', 'destination' => 'China', 'status' => 'active']);

        $this->actingAs($this->user)
            ->get(route('packages.index'))
            ->assertOk()
            ->assertSee('BRAND NEW PKG');
    }

    public function test_package_departures_count_excludes_past_when_hidden(): void
    {
        $package = Package::create(['name' => 'COUNT PKG', 'destination' => 'INDONESIA', 'status' => 'active']);
        Departure::create([
            'package_id' => $package->id,
            'departure_date' => now()->subWeek()->toDateString(),
            'return_date' => now()->subDay()->toDateString(),
            'total_seats' => 20,
            'status' => 'open',
        ]);
        Departure::create([
            'package_id' => $package->id,
            'departure_date' => now()->addWeeks(2)->toDateString(),
            'return_date' => now()->addWeeks(3)->toDateString(),
            'total_seats' => 20,
            'status' => 'open',
        ]);

        $hidden = $this->actingAs($this->user)->get(route('packages.index'));
        $this->assertStringContainsString(
            '<td class="px-6 py-4 text-center text-charcoal font-semibold">1</td>',
            $hidden->getContent(),
        );

        $included = $this->actingAs($this->user)->get(route('packages.index', ['past' => '1']));
        $this->assertStringContainsString(
            '<td class="px-6 py-4 text-center text-charcoal font-semibold">2</td>',
            $included->getContent(),
        );
    }

    public function test_reports_still_show_past_departures(): void
    {
        $this->createDeparture('HISTORY TRIP', 'INDONESIA', now()->subMonth()->toDateString());

        $response = $this->actingAs($this->user)
            ->get(route('reports.index', ['month' => '', 'year' => '']));

        $response->assertOk()->assertSee('HISTORY TRIP');
    }

    public function test_departures_can_be_filtered_by_seat_availability(): void
    {
        $available = $this->createDeparture('OPEN SEATS TRIP', 'INDONESIA', '2026-09-15'); // 0 pax, 30 seats
        $almostFull = $this->createDeparture('ALMOST FULL TRIP', 'INDONESIA', '2026-09-15'); // 28 pax -> 2 seats
        $full = $this->createDeparture('FULL TRIP', 'INDONESIA', '2026-09-15'); // 30 pax -> 0 seats

        // 28 pax -> 2 seats remaining (almost full)
        Registration::create([
            'departure_id' => $almostFull->id,
            'name' => 'Group A',
            'phone' => '0123456789',
            'pax' => 28,
            'need_partner' => false,
        ]);

        // 30 pax -> 0 seats remaining (full)
        Registration::create([
            'departure_id' => $full->id,
            'name' => 'Group B',
            'phone' => '0198765432',
            'pax' => 30,
            'need_partner' => false,
        ]);

        $this->actingAs($this->user)
            ->get(route('departures.index', ['seat' => 'full']))
            ->assertOk()
            ->assertSee('FULL TRIP')
            ->assertDontSee(route('departures.show', $almostFull))
            ->assertDontSee(route('departures.show', $available));

        $this->actingAs($this->user)
            ->get(route('departures.index', ['seat' => 'almost_full']))
            ->assertOk()
            ->assertSee('ALMOST FULL TRIP')
            ->assertDontSee(route('departures.show', $full))
            ->assertDontSee(route('departures.show', $available));

        $this->actingAs($this->user)
            ->get(route('departures.index', ['seat' => 'available']))
            ->assertOk()
            ->assertSee('OPEN SEATS TRIP')
            ->assertDontSee(route('departures.show', $almostFull))
            ->assertDontSee(route('departures.show', $full));
    }

    public function test_seat_filter_excludes_cancelled_trips(): void
    {
        $cancelled = $this->createDeparture('CANCELLED TRIP', 'INDONESIA', '2026-09-15');
        $cancelled->update(['status' => 'cancelled']);

        $this->actingAs($this->user)
            ->get(route('departures.index', ['seat' => 'available']))
            ->assertOk()
            ->assertDontSee(route('departures.show', $cancelled));
    }

    public function test_trip_list_filter_helper_applies_seat_filter(): void
    {
        $pkg = Package::create(['name' => 'SEAT PKG', 'destination' => 'INDONESIA', 'status' => 'active']);

        $full = Departure::create([
            'package_id' => $pkg->id,
            'departure_date' => '2026-09-01',
            'return_date' => '2026-09-08',
            'total_seats' => 10,
            'status' => 'open',
        ]);

        $open = Departure::create([
            'package_id' => $pkg->id,
            'departure_date' => '2026-09-02',
            'return_date' => '2026-09-09',
            'total_seats' => 10,
            'status' => 'open',
        ]);

        Registration::create([
            'departure_id' => $full->id,
            'name' => 'Full Group',
            'phone' => '0123456789',
            'pax' => 10,
            'need_partner' => false,
        ]);

        $filter = new TripListFilter('departures', 'departures.index', ['seat' => 'full']);

        $results = $filter->applyToDepartureQuery(Departure::query())->get();

        $this->assertCount(1, $results);
        $this->assertSame($full->id, $results->first()->id);
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
