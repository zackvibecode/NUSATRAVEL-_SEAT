<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;

class KeepMakassarPackagesCommand extends Command
{
    protected $signature = 'packages:keep-makassar';

    protected $description = 'Ensure Makassar package exists and hard-delete all other packages';

    public function handle(): int
    {
        $makassar = Package::query()
            ->where(function ($query) {
                $query->where('name', 'like', '%Makassar%')
                    ->orWhere('destination', 'like', '%Makassar%');
            })
            ->first();

        if (! $makassar) {
            $makassar = Package::create([
                'name' => 'Makassar 5D4N',
                'destination' => 'Indonesia',
                'description' => 'Makassar, Bantimurung, Rammang-Rammang',
                'status' => 'active',
            ]);
            $this->info("Created Makassar package #{$makassar->id}.");
        } else {
            $this->info("Keeping Makassar package #{$makassar->id} ({$makassar->name}).");
        }

        $toDelete = Package::query()->where('id', '!=', $makassar->id)->get();
        $count = $toDelete->count();

        foreach ($toDelete as $package) {
            $package->delete();
        }

        $this->info("Deleted {$count} other package(s). Related departures and registrations were removed.");

        return self::SUCCESS;
    }
}
