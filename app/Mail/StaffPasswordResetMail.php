<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $resetUrl,
        public string $email,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your LeaveFlow password — EVSU Ormoc Campus',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.staff-password-reset',
            text: 'emails.staff-password-reset-text',
        );
    }
}
