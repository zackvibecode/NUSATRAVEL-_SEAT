<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Support\TripListFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Monthly report: totals and per-departure breakdown (PRD section 12).
     */
    public function index(Request $request): View
    {
        $filter = TripListFilter::fromRequest($request, 'reports', 'reports.index');

        if (! $request->has('month') && ! $request->has('year')) {
            $filter->month = now()->month;
            $filter->year = now()->year;
        }

        $query = Departure::query()->with('package', 'registrations');
        $departures = $filter->applyToDepartureQuery($query)->get();

        $totalDepartures = $departures->count();
        $totalCapacity = $departures->sum('total_seats');
        $registeredPax = $departures->sum(fn ($d) => $d->registered_pax);
        $availableSeats = $totalCapacity - $registeredPax;
        $overallOccupancy = $totalCapacity > 0
            ? round(($registeredPax / $totalCapacity) * 100, 1)
            : 0;

        $periodLabel = match (true) {
            $filter->month !== null && $filter->year !== null => TripListFilter::months()[$filter->month].' '.$filter->year,
            $filter->year !== null => (string) $filter->year,
            $filter->month !== null => TripListFilter::months()[$filter->month],
            default => 'All time',
        };

        return view('reports.index', [
            'filter' => $filter,
            'departures' => $departures,
            'periodLabel' => $periodLabel,
            'totalDepartures' => $totalDepartures,
            'totalCapacity' => $totalCapacity,
            'registeredPax' => $registeredPax,
            'availableSeats' => $availableSeats,
            'overallOccupancy' => $overallOccupancy,
        ]);
    }
}
