<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $token,
        public string $email,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your SeatWeb password',
        );
    }

    public function content(): Content
    {
        $url = url('/reset-password/'.$this->token.'?email='.urlencode($this->email));

        return new Content(
            markdown: 'mail.reset-password',
            with: [
                'url' => $url,
                'expires' => '1 hour',
            ],
        );
    }
}
