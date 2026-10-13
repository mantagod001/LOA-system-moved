<?php

namespace App\Mail;

use App\Models\LoaRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoaDiscontinuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LoaRequest $loa,
        public string $statusUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "LOA Discontinued — {$this->loa->control_number} | EVSU Ormoc Campus",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.loa-discontinued',
            text: 'emails.loa-discontinued-text',
        );
    }
}
