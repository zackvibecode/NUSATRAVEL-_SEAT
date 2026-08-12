<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Services\SeatMetricsService;
use App\Support\TripListFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Monthly report: totals and per-departure breakdown (PRD section 12).
     */
    public function index(Request $request, SeatMetricsService $metrics): View
    {
        $filter = TripListFilter::fromRequest($request, 'reports', 'reports.index');

        if (! $request->has('month') && ! $request->has('year')) {
            $filter->month = now()->month;
            $filter->year = now()->year;
        }

        $query = Departure::query()
            ->with('package')
            ->withSum('registrations as registered_pax_sum', 'pax');

        $departures = $filter->applyToDepartureQuery($query)->paginate(20);

        $stats = $metrics->calculate($departures->getCollection());

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
            'totalDepartures' => $departures->total(),
            'totalCapacity' => $stats['totalCapacity'],
            'registeredPax' => $stats['registeredPax'],
            'availableSeats' => $stats['availableSeats'],
            'overallOccupancy' => $stats['overallOccupancy'],
        ]);
    }
}
