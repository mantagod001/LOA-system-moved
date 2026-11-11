<?php

namespace Tests\Feature;

use App\Enums\ApprovalStageStatus;
use App\Mail\LoaReturnReminderMail;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveReturnReminderTest extends TestCase
{
    use RefreshDatabase;

    private Department $dcs;
    private Program $bsit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->dcs = Department::query()->where('code', 'DCS')->firstOrFail();
        $this->bsit = Program::query()->where('code', 'BSIT')->firstOrFail();
    }

    public function test_reminder_is_sent_once_seven_days_before_return(): void
    {
        Mail::fake();

        $due = $this->createApprovedLeave(now()->addDays(7)->toDateString(), 'due@evsu.edu.ph');
        $this->createApprovedLeave(now()->addDays(6)->toDateString(), 'soon@evsu.edu.ph');
        $this->createApprovedLeave(now()->addDays(8)->toDateString(), 'later@evsu.edu.ph');
        $returned = $this->createApprovedLeave(now()->addDays(7)->toDateString(), 'back@evsu.edu.ph');
        $returned->forceFill(['returned_at' => now()])->save();

        $this->artisan('loa:send-return-reminders')->assertSuccessful();

        Mail::assertSent(LoaReturnReminderMail::class, fn ($mail) => $mail->hasTo('due@evsu.edu.ph'));
        Mail::assertNotSent(LoaReturnReminderMail::class, fn ($mail) => $mail->hasTo('soon@evsu.edu.ph'));
        Mail::assertNotSent(LoaReturnReminderMail::class, fn ($mail) => $mail->hasTo('later@evsu.edu.ph'));
        Mail::assertNotSent(LoaReturnReminderMail::class, fn ($mail) => $mail->hasTo('back@evsu.edu.ph'));
        Mail::assertSent(LoaReturnReminderMail::class, 1);
        $this->assertNotNull($due->fresh()->return_reminder_sent_at);

        $this->artisan('loa:send-return-reminders')->assertSuccessful();
        Mail::assertSent(LoaReturnReminderMail::class, 1);
    }

    public function test_parent_with_an_approved_extension_is_not_reminded(): void
    {
        Mail::fake();

        $parent = $this->createApprovedLeave(now()->addDays(7)->toDateString(), 'parent@evsu.edu.ph');
        $extension = $this->createApprovedLeave(now()->addDays(7)->toDateString(), 'parent@evsu.edu.ph');
        $extension->forceFill([
            'is_extension' => true,
            'parent_loa_id' => $parent->id,
            'student_id' => $parent->student_id,
        ])->save();

        $this->artisan('loa:send-return-reminders')->assertSuccessful();

        Mail::assertNotSent(LoaReturnReminderMail::class, fn ($mail) => $mail->loa->is($parent));
        Mail::assertSent(LoaReturnReminderMail::class, fn ($mail) => $mail->loa->is($extension));
    }

    private function createApprovedLeave(string $returnDate, string $email): LoaRequest
    {
        $token = LoaAccessToken::create([
            'student_id' => fake()->numerify('####-####'),
            'full_name' => 'Ana Reyes',
            'email' => $email,
            'token_hash' => hash('sha256', Str::random(40)),
            'expires_at' => now()->addHours(24),
            'otp_verified' => true,
        ]);

        $loa = LoaRequest::create([
            'loa_access_token_id' => $token->id,
            'student_id' => $token->student_id,
            'full_name' => $token->full_name,
            'email' => $email,
            'department_id' => $this->dcs->id,
            'program_id' => $this->bsit->id,
            'year_level' => 'BSIT-1st',
            'start_date' => now()->subMonth()->toDateString(),
            'return_date' => $returnDate,
            'reason' => 'Medical leave.',
            'parent_full_name' => 'Maria Reyes',
            'parent_relationship' => 'Mother',
            'parent_phone' => '917-123-4567',
            'status' => 'approved',
            'submitted_at' => now()->subMonth(),
            'dept_head_status' => ApprovalStageStatus::Approved,
            'saso_status' => ApprovalStageStatus::Approved,
            'campus_director_status' => ApprovalStageStatus::Approved,
        ]);
        $loa->assignControlNumber();
        $loa->save();

        return $loa->fresh();
    }
}
