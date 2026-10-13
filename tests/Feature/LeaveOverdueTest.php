<?php

namespace Tests\Feature;

use App\Enums\ApprovalStageStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveOverdueTest extends TestCase
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

    public function test_approved_leave_past_return_date_is_overdue(): void
    {
        $loa = $this->createApprovedLeave(now()->subDay()->toDateString());

        $this->assertTrue($loa->isOverdue());
        $this->assertGreaterThan(0, $loa->daysOverdue());
    }

    public function test_future_return_date_is_not_overdue(): void
    {
        $loa = $this->createApprovedLeave(now()->addMonth()->toDateString());

        $this->assertFalse($loa->isOverdue());
    }

    public function test_parent_is_not_overdue_when_extension_is_approved(): void
    {
        $parent = $this->createApprovedLeave(now()->subDay()->toDateString());
        $this->createApprovedExtension($parent, now()->addMonth()->toDateString());

        $this->assertFalse($parent->fresh()->isOverdue());
    }

    public function test_dashboard_lists_overdue_and_staff_can_mark_returned(): void
    {
        $loa = $this->createApprovedLeave(now()->subDays(3)->toDateString());
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('Overdue returns')
            ->assertSeeText($loa->control_number)
            ->assertSeeText('Expected return date has passed');

        $this->actingAs($saso)
            ->get(route('staff.dashboard', ['status' => 'overdue']))
            ->assertOk()
            ->assertSeeText($loa->control_number);

        $this->actingAs($saso)
            ->post(route('staff.loa.returned', $loa))
            ->assertRedirect(route('staff.loa.show', $loa));

        $fresh = $loa->fresh();
        $this->assertNotNull($fresh->returned_at);
        $this->assertSame($saso->id, $fresh->returned_by);
        $this->assertFalse($fresh->isOverdue());

        $this->actingAs($saso)
            ->get(route('staff.loa.show', $loa))
            ->assertOk()
            ->assertSeeText('Marked as returned');

        $this->actingAs($saso)
            ->get(route('staff.dashboard', ['status' => 'overdue']))
            ->assertOk()
            ->assertDontSeeText($loa->control_number, false);
    }

    public function test_department_head_cannot_mark_other_dept_returned(): void
    {
        $loa = $this->createApprovedLeave(now()->subDay()->toDateString());
        $dte = Department::query()->where('code', 'DTE')->firstOrFail();
        $otherDh = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $dte->id,
        ]);

        $this->actingAs($otherDh)
            ->post(route('staff.loa.returned', $loa))
            ->assertForbidden();
    }

    public function test_student_status_shows_overdue_notice(): void
    {
        $loa = $this->createApprovedLeave(now()->subDay()->toDateString());

        $this->post(route('student.status'), [
            'control_number' => $loa->control_number,
            'student_id' => $loa->student_id,
        ])->assertOk()
            ->assertSeeText('Expected return date has passed');
    }

    private function createApprovedLeave(string $returnDate): LoaRequest
    {
        $token = LoaAccessToken::create([
            'student_id'   => fake()->numerify('####-####'),
            'full_name'    => 'Ana Reyes',
            'email'        => fake()->unique()->safeEmail(),
            'token_hash'   => hash('sha256', Str::random(40)),
            'expires_at'   => now()->addHours(24),
            'otp_verified' => true,
        ]);

        $loa = LoaRequest::create([
            'loa_access_token_id' => $token->id,
            'student_id'          => $token->student_id,
            'full_name'           => $token->full_name,
            'email'               => $token->email,
            'department_id'       => $this->dcs->id,
            'program_id'          => $this->bsit->id,
            'year_level'          => 'BSIT-1st',
            'start_date'          => now()->subMonths(2)->toDateString(),
            'return_date'         => $returnDate,
            'reason'              => 'Medical leave.',
            'parent_full_name'    => 'Maria Reyes',
            'parent_relationship' => 'Mother',
            'parent_phone'        => '+639171234567',
            'status'              => 'approved',
            'submitted_at'        => now()->subMonths(2),
            'dept_head_status'    => ApprovalStageStatus::Approved,
            'saso_status'         => ApprovalStageStatus::Approved,
            'campus_director_status' => ApprovalStageStatus::Approved,
        ]);
        $loa->assignControlNumber();
        $loa->save();

        return $loa->fresh();
    }

    private function createApprovedExtension(LoaRequest $parent, string $returnDate): LoaRequest
    {
        $token = LoaAccessToken::create([
            'student_id'   => $parent->student_id,
            'full_name'    => $parent->full_name,
            'email'        => $parent->email,
            'token_hash'   => hash('sha256', Str::random(40)),
            'expires_at'   => now()->addHours(24),
            'otp_verified' => true,
            'purpose'      => 'extension',
            'parent_loa_id'=> $parent->id,
        ]);

        $loa = LoaRequest::create([
            'loa_access_token_id' => $token->id,
            'parent_loa_id'       => $parent->id,
            'is_extension'        => true,
            'student_id'          => $parent->student_id,
            'full_name'           => $parent->full_name,
            'email'               => $parent->email,
            'department_id'       => $parent->department_id,
            'program_id'          => $parent->program_id,
            'start_date'          => $parent->start_date,
            'return_date'         => $returnDate,
            'reason'              => 'Need more time.',
            'status'              => 'approved',
            'submitted_at'        => now(),
            'president_status'    => ApprovalStageStatus::Approved,
            'dept_head_status'    => ApprovalStageStatus::Approved,
            'saso_status'         => ApprovalStageStatus::Approved,
            'campus_director_status' => ApprovalStageStatus::Approved,
        ]);
        $loa->assignControlNumber();
        $loa->save();

        return $loa->fresh();
    }
}
