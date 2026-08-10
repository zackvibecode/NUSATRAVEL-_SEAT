<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Monthly report: totals and per-departure breakdown (PRD section 12).
     */
    public function index(Request $request): View
    {
        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        $departures = Departure::with('package', 'registrations')
            ->whereMonth('departure_date', $month)
            ->whereYear('departure_date', $year)
            ->orderBy('departure_date')
            ->get();

        $totalDepartures = $departures->count();
        $totalCapacity = $departures->sum('total_seats');
        $registeredPax = $departures->sum(fn ($d) => $d->registered_pax);
        $availableSeats = $totalCapacity - $registeredPax;
        $overallOccupancy = $totalCapacity > 0
            ? round(($registeredPax / $totalCapacity) * 100, 1)
            : 0;

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return view('reports.index', [
            'departures' => $departures,
            'months' => $months,
            'month' => $month,
            'year' => $year,
            'totalDepartures' => $totalDepartures,
            'totalCapacity' => $totalCapacity,
            'registeredPax' => $registeredPax,
            'availableSeats' => $availableSeats,
            'overallOccupancy' => $overallOccupancy,
        ]);
    }
}
