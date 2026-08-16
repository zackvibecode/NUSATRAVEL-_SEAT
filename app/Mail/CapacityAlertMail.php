<?php

namespace App\Mail;

use App\Models\Departure;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class CapacityAlertMail extends Mailable
{
    use Queueable;

    /**
     * @param  Collection<int, Departure>  $trips
     */
    public function __construct(
        public readonly Collection $trips,
        public readonly int $threshold,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf('[SeatWeb] %d trip(s) nearly full', $this->trips->count()),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.capacity-alert',
        );
    }
}
