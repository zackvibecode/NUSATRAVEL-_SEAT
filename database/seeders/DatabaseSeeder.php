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

        $turkiye = Package::create([
            'name' => 'Türkiye Classic 8D6N',
            'destination' => 'Türkiye',
            'description' => 'Istanbul, Cappadocia, Pamukkale',
            'status' => 'active',
        ]);

        $yunnan = Package::create([
            'name' => 'Yunnan 8D6N',
            'destination' => 'China',
            'description' => 'Kunming, Dali, Lijiang, Shangri-La',
            'status' => 'active',
        ]);

        $sichuan = Package::create([
            'name' => 'Sichuan 7D5N',
            'destination' => 'China',
            'description' => 'Chengdu, Jiuzhaigou',
            'status' => 'active',
        ]);

        $harbin = Package::create([
            'name' => 'Harbin 5D4N',
            'destination' => 'China',
            'description' => 'Harbin Ice & Snow Festival',
            'status' => 'active',
        ]);

        $archived = Package::create([
            'name' => 'Bali 4D3N',
            'destination' => 'Indonesia',
            'description' => 'Old package (archived)',
            'status' => 'archived',
        ]);

        $departureData = [
            [$turkiye->id, now()->addDays(17), now()->addDays(25), 25, 8999, 'Turkish Airlines'],
            [$yunnan->id, now()->addDays(34), now()->addDays(42), 25, 7299, 'AirAsia'],
            [$sichuan->id, now()->addDays(48), now()->addDays(55), 25, 6999, 'AirAsia'],
            [$harbin->id, now()->addDays(123), now()->addDays(128), 30, 7899, 'China Southern'],
            [$yunnan->id, now()->addDays(133), now()->addDays(141), 25, 7299, 'AirAsia'],
            [$archived->id, now()->subDays(30), now()->subDays(23), 20, 4999, 'AirAsia'],
        ];

        $departureIds = [];

        foreach ($departureData as [$packageId, $departureDate, $returnDate, $seats, $price, $airline]) {
            $departureIds[] = Departure::create([
                'package_id' => $packageId,
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
            [$departureIds[0], 'Firdaus', '017-2223334', 2, false, null, null],
            [$departureIds[1], 'Nurul Huda', '016-5556667', 3, false, null, 'Friends group'],
            [$departureIds[1], 'Razif', '019-7778889', 1, true, 'male', 'Solo traveller'],
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
