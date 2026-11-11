<?php

namespace App\Console\Commands;

use App\Mail\LoaReturnReminderMail;
use App\Models\LoaRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReturnReminders extends Command
{
    protected $signature = 'loa:send-return-reminders';

    protected $description = 'Email students whose approved leave ends in 7 days';

    public function handle(): int
    {
        $returnDate = now()->addDays(7)->toDateString();
        $sent = 0;

        LoaRequest::query()
            ->where('status', 'approved')
            ->whereDate('return_date', $returnDate)
            ->whereNull('return_reminder_sent_at')
            ->whereNull('returned_at')
            ->whereNull('did_not_enroll_at')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereDoesntHave('extensions', function ($extensions) {
                $extensions->where('status', 'approved');
            })
            ->orderBy('id')
            ->each(function (LoaRequest $loa) use (&$sent) {
                try {
                    Mail::to($loa->email)->send(new LoaReturnReminderMail($loa, route('student.extend')));
                    $loa->forceFill(['return_reminder_sent_at' => now()])->save();
                    $sent++;
                } catch (\Throwable $e) {
                    report($e);
                    $this->error("Reminder failed for {$loa->control_number}: {$e->getMessage()}");
                }
            });

        $this->info("Sent {$sent} return reminder ".($sent === 1 ? 'email' : 'emails')." for {$returnDate}.");

        return self::SUCCESS;
    }
}
