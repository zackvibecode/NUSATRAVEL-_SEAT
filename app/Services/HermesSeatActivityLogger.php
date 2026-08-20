<?php

namespace App\Services;

use App\Models\Departure;
use App\Models\HermesSeatActivity;

class HermesSeatActivityLogger
{
    public function record(
        Departure $departure,
        int $delta,
        ?string $activityType = null,
        ?string $actorName = null,
        ?string $activityNote = null,
    ): void {
        if ($delta === 0) {
            return;
        }

        $departure->loadMissing('package');

        HermesSeatActivity::create([
            'departure_id' => $departure->id,
            'package_name' => $departure->package?->name ?? '',
            'departure_date' => $departure->departure_date?->toDateString(),
            'seat_delta' => $delta,
            'activity_type' => $activityType,
            'actor_name' => $actorName,
            'activity_note' => $activityNote,
        ]);
    }

    /**
     * @param  array{registered: int, total: int}  $before
     */
    public function recordFromSnapshot(
        Departure $departure,
        array $before,
        ?string $activityType = 'import',
        ?string $actorName = null,
        ?string $activityNote = null,
    ): void {
        $delta = ($departure->registered_pax - $before['registered'])
            + ($departure->total_seats - $before['total']);

        $this->record($departure, $delta, $activityType, $actorName, $activityNote);
    }

    /**
     * @param  iterable<int, Departure>  $departures
     * @return array<int, array{registered: int, total: int}>
     */
    public function snapshotOccupancy(iterable $departures): array
    {
        $snapshot = [];

        foreach ($departures as $departure) {
            $snapshot[$departure->id] = [
                'registered' => $departure->registered_pax,
                'total' => $departure->total_seats,
            ];
        }

        return $snapshot;
    }
}
