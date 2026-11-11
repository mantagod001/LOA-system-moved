<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OutboundMail
{
    /**
     * Send after the browser already has its response, so Gmail does not hold the page.
     * The message still goes out in this same request. No queue worker is required.
     */
    public static function afterResponse(string $to, Mailable $mailable): void
    {
        if ($to === '') {
            return;
        }

        app()->terminating(function () use ($to, $mailable): void {
            try {
                Mail::to($to)->send($mailable);
            } catch (Throwable $e) {
                report($e);
            }
        });
    }
}
