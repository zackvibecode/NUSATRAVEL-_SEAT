<?php

namespace App\Console\Commands;

use App\Mail\CapacityAlertMail;
use App\Models\Departure;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCapacityAlertsCommand extends Command
{
    protected $signature = 'seatweb:send-capacity-alerts';

    protected $description = 'Email staff about upcoming trips that are full or almost full';

    public function handle(): int
    {
        $threshold = (int) config('seatweb.alert_threshold', 5);

        $trips = Departure::query()
            ->with('package')
            ->withSum('registrations as registered_pax_sum', 'pax')
            ->notCancelled()
            ->where('departure_date', '>=', now()->toDateString())
            ->where('departure_date', '<=', now()->addDays((int) config('seatweb.alert_days_ahead', 30))->toDateString())
            ->get()
            ->filter(fn ($d) => $d->available_seats <= $threshold)
            ->sortBy('departure_date');

        if ($trips->isEmpty()) {
            $this->info('No trips near capacity — nothing to send.');

            return self::SUCCESS;
        }

        $recipients = User::query()->pluck('email');

        if ($recipients->isEmpty()) {
            $this->warn('No staff users to notify.');

            return self::SUCCESS;
        }

        foreach ($recipients as $email) {
            Mail::to($email)->send(new CapacityAlertMail($trips, $threshold));
        }

        $this->info(sprintf('Sent capacity alert for %d trip(s) to %d recipient(s).', $trips->count(), $recipients->count()));

        return self::SUCCESS;
    }
}
