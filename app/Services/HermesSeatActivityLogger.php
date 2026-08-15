<?php

namespace App\Services;

use App\Models\Departure;
use App\Models\HermesSeatActivity;

class HermesSeatActivityLogger
{
    public function record(Departure $departure, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

        $departure->loadMissing('package');

        HermesSeatActivity::create([
            'departure_id' => $departure->id,
            'package_name' => $departure->package?->name ?? '',
            'departure_date' => $departure->departure_date?->toDateString(),
            'seat_delta' => $delta,
        ]);
    }

    /**
     * @param  iterable<int, Departure>  $departures
     * @return array<int, int> departure id => available seats
     */
    public function snapshotAvailable(iterable $departures): array
    {
        $snapshot = [];

        foreach ($departures as $departure) {
            $snapshot[$departure->id] = $departure->available_seats;
        }

        return $snapshot;
    }
}
