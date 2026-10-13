<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $email,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your LeaveFlow password reset code — EVSU Ormoc Campus',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.staff-password-otp',
            text: 'emails.staff-password-otp-text',
        );
    }
}
