<?php

namespace App\Mail;

use App\Models\LoaRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoaReturnReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LoaRequest $loa,
        public string $extendUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your leave ends in 7 days — {$this->loa->control_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.loa-return-reminder',
            text: 'emails.loa-return-reminder-text',
        );
    }
}
