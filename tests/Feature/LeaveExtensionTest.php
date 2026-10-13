<?php

namespace Tests\Feature;

use App\Enums\ApprovalStageStatus;
use App\Enums\UserRole;
use App\Mail\LoaOtpMail;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveExtensionTest extends TestCase
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

    public function test_extend_page_renders(): void
    {
        $this->get(route('student.extend'))
            ->assertOk()
            ->assertSeeText('Extend an approved leave')
            ->assertDontSee('name="first_name"', false)
            ->assertDontSeeText('First name');
    }

    public function test_cannot_extend_without_approved_leave(): void
    {
        Mail::fake();

        $this->from(route('student.extend'))
            ->post(route('student.extend.store'), [
                'student_id' => '2021-0099',
                'email' => 'ana@evsu.edu.ph',
            ])
            ->assertSessionHasErrors(['email']);

        Mail::assertNothingSent();
    }

    public function test_cannot_extend_when_id_and_email_do_not_match_same_leave(): void
    {
        Mail::fake();

        $this->createApprovedLeave('2021-0100', 'ana@evsu.edu.ph');
        $this->createApprovedLeave('2021-0101', 'lee@evsu.edu.ph');

        $this->from(route('student.extend'))
            ->post(route('student.extend.store'), [
                'student_id' => '2021-0100',
                'email' => 'lee@evsu.edu.ph',
            ])
            ->assertSessionHasErrors(['email', 'student_id']);

        Mail::assertNothingSent();
    }

    public function test_approved_leave_blocks_new_request_and_allows_extension(): void
    {
        Mail::fake();

        $parent = $this->createApprovedLeave('2021-0100', 'ana@evsu.edu.ph');

        $this->from(route('student.identity'))
            ->post(route('student.identity.store'), [
                'student_id' => '2021-0100',
                'first_name' => 'Ana',
                'last_name' => 'Reyes',
                'email' => 'ana@evsu.edu.ph',
            ])
            ->assertSessionHasErrors(['email']);

        $this->post(route('student.extend.store'), [
            'student_id' => '2021-0100',
            'email' => 'ana@evsu.edu.ph',
        ])->assertRedirect();

        Mail::assertSent(LoaOtpMail::class);

        $token = LoaAccessToken::query()->where('parent_loa_id', $parent->id)->firstOrFail();
        $this->assertSame('extension', $token->purpose);
        $this->assertSame('Ana', $token->first_name);
        $this->assertSame('Reyes', $token->last_name);
    }

    public function test_extension_form_is_prefilled_and_locks_identity(): void
    {
        $parent = $this->createApprovedLeave('2021-0500', 'mia@evsu.edu.ph');
        $plain = Str::random(40);

        $token = LoaAccessToken::create([
            'student_id'    => $parent->student_id,
            'full_name'     => $parent->full_name,
            'first_name'    => $parent->first_name,
            'last_name'     => $parent->last_name,
            'email'         => $parent->email,
            'token_hash'    => LoaAccessToken::hashPlainToken($plain),
            'expires_at'    => now()->addHours(24),
            'otp_verified'  => true,
            'purpose'       => 'extension',
            'parent_loa_id' => $parent->id,
        ]);

        $this->get(route('student.form.show', $plain))
            ->assertOk()
            ->assertSeeText('From your approved leave')
            ->assertSeeText('Ana Reyes')
            ->assertSeeText('2021-0500')
            ->assertSee('name="student_id"', false)
            ->assertDontSee('id="first_name"', false)
            ->assertDontSee('data-department-filter', false);

        $this->assertDatabaseHas('loa_requests', [
            'loa_access_token_id' => $token->id,
            'is_extension' => true,
            'parent_loa_id' => $parent->id,
            'student_id' => '2021-0500',
            'status' => 'draft',
        ]);
    }

    public function test_expired_extension_form_link_returns_to_extend_page(): void
    {
        $plain = Str::random(40);

        LoaAccessToken::create([
            'student_id'   => '2021-0600',
            'full_name'    => 'Ana Reyes',
            'email'        => 'ana@evsu.edu.ph',
            'token_hash'   => LoaAccessToken::hashPlainToken($plain),
            'expires_at'   => now()->subHour(),
            'otp_verified' => true,
            'purpose'      => 'extension',
        ]);

        $this->get(route('student.form.show', $plain))
            ->assertRedirect(route('student.extend'));
    }

    public function test_president_cannot_approve_regular_leave(): void
    {
        $loa = $this->createSubmittedLeave();
        $president = User::factory()->create(['role' => UserRole::President]);

        $this->actingAs($president)
            ->post(route('staff.loa.approve', $loa))
            ->assertForbidden();

        $this->actingAs($president)
            ->get(route('staff.loa.show', $loa))
            ->assertForbidden();
    }

    public function test_president_sees_only_extensions(): void
    {
        $regular = $this->createSubmittedLeave();
        $parent = $this->createApprovedLeave('2021-0200', 'ext@evsu.edu.ph');
        $extension = $this->createSubmittedExtension($parent);

        $president = User::factory()->create(['role' => UserRole::President]);

        $this->actingAs($president)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText($extension->control_number)
            ->assertDontSeeText($regular->control_number, false);
    }

    public function test_department_head_cannot_approve_extension_before_president(): void
    {
        $parent = $this->createApprovedLeave('2021-0300', 'lee@evsu.edu.ph');
        $extension = $this->createSubmittedExtension($parent);
        $dh = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dh)
            ->post(route('staff.loa.approve', $extension))
            ->assertForbidden();
    }

    public function test_extension_runs_president_then_three_campus_steps(): void
    {
        $parent = $this->createApprovedLeave('2021-0400', 'mia@evsu.edu.ph');
        $extension = $this->createSubmittedExtension($parent);

        $president = User::factory()->create(['role' => UserRole::President]);
        $dh = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $cd = User::factory()->create(['role' => UserRole::CampusDirector]);

        $this->actingAs($president)
            ->post(route('staff.loa.approve', $extension))
            ->assertRedirect(route('staff.pipeline'));

        $fresh = $extension->fresh();
        $this->assertTrue($fresh->president_status->is(ApprovalStageStatus::Approved));
        $this->assertTrue($fresh->dept_head_status->is(ApprovalStageStatus::Pending));
        $this->assertSame('submitted', $fresh->status);

        $this->actingAs($dh)->post(route('staff.loa.approve', $extension))->assertRedirect();
        $this->actingAs($saso)->post(route('staff.loa.approve', $extension))->assertRedirect();
        $this->actingAs($cd)->post(route('staff.loa.approve', $extension))->assertRedirect();

        $done = $extension->fresh();
        $this->assertSame('approved', $done->status);
        $this->assertTrue($done->campus_director_status->is(ApprovalStageStatus::Approved));
        $this->assertStringStartsWith('EVSU-OC-EXT-', $done->control_number);
    }

    private function createSubmittedLeave(): LoaRequest
    {
        $token = LoaAccessToken::create([
            'student_id'   => fake()->numerify('####-####'),
            'full_name'    => fake()->name(),
            'email'        => fake()->unique()->safeEmail(),
            'token_hash'   => hash('sha256', Str::random(40)),
            'expires_at'   => now()->addHours(24),
            'otp_verified' => true,
            'purpose'      => 'leave',
        ]);

        $loa = LoaRequest::create([
            'loa_access_token_id' => $token->id,
            'student_id'          => $token->student_id,
            'full_name'           => $token->full_name,
            'email'               => $token->email,
            'department_id'       => $this->dcs->id,
            'program_id'          => $this->bsit->id,
            'year_level'          => 'BSIT-1st',
            'start_date'          => now()->addDays(5)->toDateString(),
            'return_date'         => now()->addDays(60)->toDateString(),
            'reason'              => fake()->paragraph(),
            'parent_full_name'    => fake()->name(),
            'parent_relationship' => 'Mother',
            'parent_phone'        => '+639171234567',
            'status'              => 'submitted',
            'submitted_at'        => now(),
            'dept_head_status'    => ApprovalStageStatus::Pending,
        ]);
        $loa->assignControlNumber();
        $loa->save();

        return $loa->fresh();
    }

    private function createApprovedLeave(string $studentId, string $email): LoaRequest
    {
        $token = LoaAccessToken::create([
            'student_id'   => $studentId,
            'full_name'    => 'Ana Reyes',
            'first_name'   => 'Ana',
            'last_name'    => 'Reyes',
            'email'        => $email,
            'token_hash'   => hash('sha256', Str::random(40)),
            'expires_at'   => now()->subHour(),
            'used_at'      => now()->subDay(),
            'otp_verified' => true,
            'purpose'      => 'leave',
        ]);

        $loa = LoaRequest::create([
            'loa_access_token_id' => $token->id,
            'student_id'          => $studentId,
            'first_name'          => 'Ana',
            'last_name'           => 'Reyes',
            'full_name'           => 'Ana Reyes',
            'email'               => $email,
            'department_id'       => $this->dcs->id,
            'program_id'          => $this->bsit->id,
            'year_level'          => 'BSIT-1st',
            'start_date'          => now()->subMonth()->toDateString(),
            'return_date'         => now()->addMonths(2)->toDateString(),
            'reason'              => 'Medical treatment.',
            'parent_first_name'   => 'Maria',
            'parent_last_name'    => 'Reyes',
            'parent_full_name'    => 'Maria Reyes',
            'parent_relationship' => 'Mother',
            'parent_phone'        => '+639171234567',
            'status'              => 'approved',
            'submitted_at'        => now()->subMonth(),
            'dept_head_status'    => ApprovalStageStatus::Approved,
            'saso_status'         => ApprovalStageStatus::Approved,
            'campus_director_status' => ApprovalStageStatus::Approved,
        ]);
        $loa->assignControlNumber();
        $loa->save();

        return $loa->fresh();
    }

    private function createSubmittedExtension(LoaRequest $parent): LoaRequest
    {
        $token = LoaAccessToken::create([
            'student_id'   => $parent->student_id,
            'full_name'    => $parent->full_name,
            'email'        => $parent->email,
            'token_hash'   => hash('sha256', Str::random(40)),
            'expires_at'   => now()->addHours(24),
            'otp_verified' => true,
            'used_at'      => now(),
            'purpose'      => 'extension',
            'parent_loa_id'=> $parent->id,
        ]);

        $loa = LoaRequest::create([
            'loa_access_token_id' => $token->id,
            'parent_loa_id'       => $parent->id,
            'is_extension'        => true,
            'student_id'          => $parent->student_id,
            'first_name'          => $parent->first_name,
            'last_name'           => $parent->last_name,
            'full_name'           => $parent->full_name,
            'email'               => $parent->email,
            'department_id'       => $parent->department_id,
            'program_id'          => $parent->program_id,
            'year_level'          => $parent->year_level,
            'start_date'          => $parent->start_date,
            'return_date'         => $parent->return_date->copy()->addMonths(6)->toDateString(),
            'reason'              => 'Need additional recovery time.',
            'parent_full_name'    => $parent->parent_full_name,
            'parent_relationship' => $parent->parent_relationship,
            'parent_phone'        => $parent->parent_phone,
            'status'              => 'submitted',
            'submitted_at'        => now(),
            'president_status'    => ApprovalStageStatus::Pending,
        ]);
        $loa->assignControlNumber();
        $loa->save();

        return $loa->fresh();
    }
}
