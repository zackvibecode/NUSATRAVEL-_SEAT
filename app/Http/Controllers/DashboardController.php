<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\HermesSeatActivity;
use App\Models\Registration;
use App\Services\SeatMetricsService;
use App\Support\TripListFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request, SeatMetricsService $metrics)
    {
        $filter = TripListFilter::fromRequest($request, 'dashboard', 'dashboard');

        $query = Departure::query()
            ->with('package')
            ->withSum('registrations as registered_pax_sum', 'pax');

        $upcoming = $filter->applyToDepartureQuery($query, upcomingOnly: true)->get();

        // Sales users get their own dashboard focused on selling seats.
        if (auth()->user()->isSales()) {
            return $this->salesDashboard($filter, $upcoming);
        }

        $stats = $metrics->calculate($upcoming);

        // Count by status for summary cards (derived from existing status_label)
        $almostFullTrips = $upcoming->filter(fn ($d) => $d->status_label === 'almost_full')->count();
        $attentionCount = $upcoming->filter(fn ($d) => $d->status_label === 'open')->count();

        $recentRegistrations = Registration::with('departure.package')
            ->latest()
            ->take(5)
            ->get();

        $trendMonths = collect(range(5, 0))->map(function ($offset) {
            return now()->subMonths($offset)->startOfMonth();
        });

        $trendData = $trendMonths->map(function ($month) {
            $departures = Departure::whereBetween('departure_date', [$month, $month->copy()->endOfMonth()])
                ->withSum('registrations as registered_pax_sum', 'pax')
                ->get();

            return [
                'label' => $month->format('M'),
                'pax' => $departures->sum('registered_pax_sum'),
            ];
        });

        return view('dashboard', [
            'filter' => $filter,
            'upcomingTrips' => $upcoming->count(),
            'totalCapacity' => $stats['totalCapacity'],
            'registeredPax' => $stats['registeredPax'],
            'availableSeats' => $stats['availableSeats'],
            'overallOccupancy' => $stats['overallOccupancy'],
            'almostFullTrips' => $almostFullTrips,
            'attentionCount' => $attentionCount,
            'upcomingDepartures' => $upcoming->take(5),
            'recentRegistrations' => $recentRegistrations,
            'trendData' => $trendData,
            'hermesSeatActivities' => HermesSeatActivity::query()->latest()->take(8)->get(),
        ]);
    }

    /**
     * Focused dashboard for the sales team: find a trip, check seats, register a customer.
     *
     * @param  Collection<int, Departure>  $upcoming
     */
    private function salesDashboard(TripListFilter $filter, $upcoming)
    {
        $almostFull = $upcoming->filter(fn ($d) => $d->status_label === 'almost_full')->count();
        $availableSeats = $upcoming->sum(fn ($d) => max(0, $d->available_seats));

        $recentRegistrations = Registration::with('departure.package')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.sales', [
            'filter' => $filter,
            'upcomingTrips' => $upcoming->count(),
            'availableSeats' => $availableSeats,
            'almostFull' => $almostFull,
            'nearestDepartures' => $upcoming->sortBy('departure_date')->take(8),
            'recentRegistrations' => $recentRegistrations,
            'hermesSeatActivities' => HermesSeatActivity::query()->latest()->take(6)->get(),
        ]);
    }

    /**
     * Full list of trips that need attention, grouped by month (earliest first).
     */
    public function attentionTrips(Request $request)
    {
        $departures = Departure::query()
            ->with('package')
            ->withSum('registrations as registered_pax_sum', 'pax')
            ->upcoming()
            ->orderBy('departure_date')
            ->get()
            ->filter(fn ($d) => $d->status_label === 'open')
            ->groupBy(fn ($d) => $d->departure_date->format('F Y'));

        return view('dashboard.attention-trips', [
            'groupedDepartures' => $departures,
        ]);
    }
}
