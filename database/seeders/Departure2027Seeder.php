<?php

namespace Database\Seeders;

use App\Models\Departure;
use App\Models\Package;
use Illuminate\Database\Seeder;

/**
 * Adds 2027 departure dates under the Makassar package so the sales team
 * can start selling next-year trips. Idempotent — safe to run on any
 * environment: existing dates are matched on (package, departure_date)
 * and skipped, never duplicated.
 *
 * Usage: php artisan db:seed --class=Departure2027Seeder
 */
class Departure2027Seeder extends Seeder
{
    public function run(): void
    {
        $package = Package::firstOrCreate(
            ['name' => 'Makassar 5D4N'],
            [
                'destination' => 'Indonesia',
                'description' => 'Makassar, Bantimurung, Rammang-Rammang',
                'status' => 'active',
            ]
        );

        $trips = [
            ['2027-01-18', '2027-01-22', 25, 3499, 'AirAsia'],
            ['2027-03-15', '2027-03-19', 25, 3599, 'Batik Air'],
            ['2027-06-14', '2027-06-18', 25, 3799, 'Garuda Indonesia'],
            ['2027-09-13', '2027-09-17', 25, 3599, 'AirAsia'],
        ];

        $created = 0;

        foreach ($trips as [$departureDate, $returnDate, $seats, $price, $airline]) {
            $departure = Departure::firstOrNew([
                'package_id' => $package->id,
                'departure_date' => $departureDate,
            ]);

            if (! $departure->exists) {
                $departure->fill([
                    'return_date' => $returnDate,
                    'total_seats' => $seats,
                    'price' => $price,
                    'airline' => $airline,
                    'status' => 'open',
                ])->save();
                $created++;
            }
        }

        $this->command?->info("Departure2027Seeder: {$created} new 2027 departure(s) added.");
    }
}
