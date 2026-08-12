<?php

namespace App\Services;

use App\Models\Departure;
use Illuminate\Support\Collection;

class SeatMetricsService
{
    /**
     * Calculate metrics for a collection of departures.
     *
     * @param Collection<int, Departure> $departures
     * @return array{totalCapacity: int, registeredPax: int, availableSeats: int, overallOccupancy: float}
     */
    public function calculate(Collection $departures): array
    {
        $totalCapacity = $departures->sum('total_seats');
        $registeredPax = $departures->sum(fn ($d) => $d->registered_pax);
        $availableSeats = $totalCapacity - $registeredPax;
        $overallOccupancy = $totalCapacity > 0
            ? round(($registeredPax / $totalCapacity) * 100, 1)
            : 0.0;

        return [
            'totalCapacity' => $totalCapacity,
            'registeredPax' => $registeredPax,
            'availableSeats' => $availableSeats,
            'overallOccupancy' => $overallOccupancy,
        ];
    }
}
