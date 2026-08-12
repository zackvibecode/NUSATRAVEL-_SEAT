<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Registration;
use App\Services\SeatMetricsService;
use App\Support\TripListFilter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, SeatMetricsService $metrics)
    {
        $filter = TripListFilter::fromRequest($request, 'dashboard', 'dashboard');

        $query = Departure::query()
            ->with('package')
            ->withSum('registrations as registered_pax_sum', 'pax');

        $upcoming = $filter->applyToDepartureQuery($query, upcomingOnly: true)->get();

        $stats = $metrics->calculate($upcoming);

        $needAttention = $upcoming
            ->filter(fn ($d) => $d->status_label === 'open')
            ->sortBy('departure_date')
            ->sortByDesc('available_seats')
            ->take(5);

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

            $revenue = $departures->sum(function ($d) {
                return $d->price ? $d->price * ($d->registered_pax_sum ?? 0) : 0;
            });

            return [
                'label' => $month->format('M'),
                'revenue' => $revenue,
                'pax' => $departures->sum('registered_pax_sum'),
            ];
        });

        return view('dashboard', [
            'filter' => $filter,
            'upcomingTrips' => $upcoming->count(),
            'totalCapacity' => $stats['totalCapacity'],
            'registeredPax' => $stats['registeredPax'],
            'availableSeats' => $stats['availableSeats'],
            'upcomingDepartures' => $upcoming,
            'needAttention' => $needAttention,
            'recentRegistrations' => $recentRegistrations,
            'trendData' => $trendData,
        ]);
    }
}
