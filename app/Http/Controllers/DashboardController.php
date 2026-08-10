<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Registration;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Departure::upcoming()
            ->with('package', 'registrations');

        // Month/year filter (PRD section 12)
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('departure_date', $request->integer('month'))
                ->whereYear('departure_date', $request->integer('year'));
        }

        $upcoming = $query->orderBy('departure_date')->get();

        $totalCapacity = $upcoming->sum('total_seats');
        $registeredPax = $upcoming->sum(fn ($d) => $d->registered_pax);
        $availableSeats = $totalCapacity - $registeredPax;

        // Trips Need Attention: upcoming Open departures, most available first (PRD 11.3)
        $needAttention = $upcoming
            ->filter(fn ($d) => $d->status_label === 'open')
            ->sortBy('departure_date')
            ->sortByDesc('available_seats')
            ->take(5);

        // Recent registrations for dashboard feed
        $recentRegistrations = Registration::with('departure.package')
            ->latest()
            ->take(5)
            ->get();

        // Occupancy trend data (last 6 months), from departure registrations
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


        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return view('dashboard', [
            'upcomingTrips' => $upcoming->count(),
            'totalCapacity' => $totalCapacity,
            'registeredPax' => $registeredPax,
            'availableSeats' => $availableSeats,
            'upcomingDepartures' => $upcoming,
            'needAttention' => $needAttention,
            'recentRegistrations' => $recentRegistrations,
            'trendData' => $trendData,
            'months' => $months,
            'selectedMonth' => $request->filled('month') ? $request->integer('month') : null,
            'selectedYear' => $request->filled('year') ? $request->integer('year') : null,
            'filterActive' => $request->filled('month') && $request->filled('year'),
        ]);
    }
}
