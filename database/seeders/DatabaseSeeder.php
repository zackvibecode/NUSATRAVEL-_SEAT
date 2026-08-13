<?php

namespace Database\Seeders;

use App\Models\Departure;
use App\Models\Package;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@seatweb.com'],
            [
                'name' => 'Admin Staff',
                'password' => 'password123',
            ]
        );

        if (Package::exists()) {
            return;
        }

        $makassar = Package::create([
            'name' => 'Makassar 5D4N',
            'destination' => 'Indonesia',
            'description' => 'Makassar, Bantimurung, Rammang-Rammang',
            'status' => 'active',
        ]);

        $departureIds = [];

        foreach (
            [
                [now()->addDays(21), now()->addDays(25), 25, 3299, 'AirAsia'],
                [now()->addDays(60), now()->addDays(64), 25, 3499, 'Garuda Indonesia'],
            ] as [$departureDate, $returnDate, $seats, $price, $airline]
        ) {
            $departureIds[] = Departure::create([
                'package_id' => $makassar->id,
                'departure_date' => $departureDate,
                'return_date' => $returnDate,
                'total_seats' => $seats,
                'price' => $price,
                'airline' => $airline,
                'status' => 'open',
            ])->id;
        }

        $registrations = [
            [$departureIds[0], 'Ahmad Ali', '012-3456789', 4, false, null, 'Family trip'],
            [$departureIds[0], 'Siti Aminah', '013-1112223', 1, true, 'female', 'Solo traveller'],
            [$departureIds[1], 'Nurul Huda', '016-5556667', 2, false, null, 'Friends group'],
        ];

        foreach ($registrations as [$departureId, $name, $phone, $pax, $needPartner, $gender, $notes]) {
            Registration::create([
                'departure_id' => $departureId,
                'name' => $name,
                'phone' => $phone,
                'pax' => $pax,
                'need_partner' => $needPartner,
                'partner_gender' => $gender,
                'notes' => $notes,
            ]);
        }
    }
}
