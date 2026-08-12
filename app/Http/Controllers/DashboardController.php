<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Registration;
use App\Support\TripListFilter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = TripListFilter::fromRequest($request, 'dashboard', 'dashboard');

        $query = Departure::query()->with('package', 'registrations');
        $upcoming = $filter->applyToDepartureQuery($query, upcomingOnly: true)->get();

        $totalCapacity = $upcoming->sum('total_seats');
        $registeredPax = $upcoming->sum(fn ($d) => $d->registered_pax);
        $availableSeats = $totalCapacity - $registeredPax;

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
                ->with('registrations')
                ->get();

            $revenue = $departures->sum(function ($d) {
                return $d->price ? $d->price * $d->registered_pax : 0;
            });

            return [
                'label' => $month->format('M'),
                'revenue' => $revenue,
                'pax' => $departures->sum(fn ($d) => $d->registered_pax),
            ];
        });

        return view('dashboard', [
            'filter' => $filter,
            'upcomingTrips' => $upcoming->count(),
            'totalCapacity' => $totalCapacity,
            'registeredPax' => $registeredPax,
            'availableSeats' => $availableSeats,
            'upcomingDepartures' => $upcoming,
            'needAttention' => $needAttention,
            'recentRegistrations' => $recentRegistrations,
            'trendData' => $trendData,
        ]);
    }
}
