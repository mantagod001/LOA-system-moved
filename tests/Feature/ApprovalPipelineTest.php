<?php

namespace Tests\Feature;

use App\Enums\ApprovalStageStatus;
use App\Enums\UserRole;
use App\Mail\LoaDiscontinuedMail;
use App\Mail\LoaRejectedMail;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApprovalPipelineTest extends TestCase
{
    use RefreshDatabase;

    private Department $dcs;
    private Department $dte;
    private Program $bsit;
    private Program $beed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->dcs = Department::query()->where('code', 'DCS')->firstOrFail();
        $this->dte = Department::query()->where('code', 'DTE')->firstOrFail();
        $this->bsit = Program::query()->where('code', 'BSIT')->firstOrFail();
        $this->beed = Program::query()->where('code', 'BEED')->firstOrFail();
    }

    /**
     * AC-3: Pipeline page renders with correct 7 column headers.
     */
    public function test_pipeline_page_renders_table_headers_and_stage_badges(): void
    {
        // 1 LOA: DH approved, SASO pending (via model simulate)
        $dhApprovedLoa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $dhApprovedLoa->forceFill([
            'dept_head_status' => ApprovalStageStatus::Approved,
            'dept_head_at' => now(),
            'saso_status' => ApprovalStageStatus::Pending,
        ])->save();

        // 1 LOA: all pending (default)
        $allPendingLoa = $this->createSubmittedLoa($this->dcs, $this->bsit);

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->get(route('staff.pipeline'))
            ->assertOk()
            // Sidebar items present (staff shell)
            ->assertSeeText('Dashboard')
            ->assertSeeText('Approval Pipeline')
            ->assertSeeText('Reports & Analytics')
            ->assertSeeText('User Profile')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Request LOA')
            // Table headers
            ->assertSeeText('App ID')
            ->assertSeeText('Student ID')
            ->assertSeeText('Student Name')
            ->assertSeeText('President Status')
            ->assertSeeText('Dept Head Status')
            ->assertSeeText('SASO Status')
            ->assertSeeText('Campus Director Status')
            ->assertDontSeeText('Registrar Status')
            ->assertDontSeeText('Guidance Status')
            ->assertSeeText('Action Options')
            // Stage badges
            ->assertSeeText('Approved')
            ->assertSeeText('Pending');
    }

    /**
     * AC-4: DH can approve own-dept pending LOA → stage advances, saso becomes pending.
     */
    public function test_dh_approve_advances_saso_to_pending(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $this->assertSame('pending', $loa->fresh()->dept_head_status->value);
        $this->assertNull($loa->fresh()->saso_status);
        $this->assertSame('submitted', $loa->fresh()->status);

        $dhDcs = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dhDcs)
            ->post(route('staff.loa.approve', $loa))
            ->assertRedirect(route('staff.pipeline'))
            ->assertSessionHas('status');

        $fresh = $loa->fresh();
        $this->assertTrue($fresh->dept_head_status->is(ApprovalStageStatus::Approved));
        $this->assertNotNull($fresh->dept_head_at);
        $this->assertSame($dhDcs->id, $fresh->dept_head_by);
        $this->assertTrue($fresh->saso_status->is(ApprovalStageStatus::Pending));
        // Overall status stays submitted (still in pipeline)
        $this->assertSame('submitted', $fresh->status);
    }

    /**
     * AC-4: SASO approve fails when DH stage still pending (sequential enforcement).
     */
    public function test_saso_approve_fails_403_when_dh_pending(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        // DH still pending
        $this->assertTrue($loa->dept_head_status->is(ApprovalStageStatus::Pending));

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->post(route('staff.loa.approve', $loa))
            ->assertForbidden();

        // No DB changes
        $fresh = $loa->fresh();
        $this->assertNull($fresh->saso_status);
        $this->assertTrue($fresh->dept_head_status->is(ApprovalStageStatus::Pending));
    }

    /**
     * AC-4: CD approve fails when SASO not done.
     */
    public function test_cd_approve_fails_403_when_saso_still_pending(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        // Move to DH-approved SASO-pending stage first
        $loa->forceFill([
            'dept_head_status' => ApprovalStageStatus::Approved,
            'dept_head_at' => now(),
            'saso_status' => ApprovalStageStatus::Pending,
        ])->save();

        $cd = User::factory()->create(['role' => UserRole::CampusDirector]);

        // SASO still pending -> CD cannot act
        $this->actingAs($cd)
            ->post(route('staff.loa.approve', $loa))
            ->assertForbidden();

        $this->assertNull($loa->fresh()->campus_director_status);
    }

    /**
     * AC-5: SASO reject short-circuits pipeline → overall 'rejected', CD stage never reached.
     */
    public function test_saso_reject_shortcircuits_overall_rejected_cd_null(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        // DH done, SASO pending
        $dhDcs = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $loa->markApprovedBy($dhDcs);
        $this->assertTrue($loa->fresh()->saso_status->is(ApprovalStageStatus::Pending));

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->post(route('staff.loa.reject', $loa), ['reason' => 'Missing supporting docs.'])
            ->assertRedirect(route('staff.pipeline'))
            ->assertSessionHas('status');

        $fresh = $loa->fresh();
        $this->assertTrue($fresh->saso_status->is(ApprovalStageStatus::Rejected));
        $this->assertSame($saso->id, $fresh->saso_by);
        $this->assertNotNull($fresh->saso_at);
        $this->assertSame('rejected', $fresh->status);
        $this->assertSame($saso->id, $fresh->rejected_by);
        $this->assertNotNull($fresh->rejected_at);
        $this->assertSame('Missing supporting docs.', $fresh->rejection_reason);
        // CD stage NEVER reached (still null)
        $this->assertNull($fresh->campus_director_status);
    }

    /**
     * AC-6: CD approve → pipeline complete, overall status becomes 'approved'.
     */
    public function test_cd_approve_sets_overall_approved(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);

        $dhDcs = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $saso  = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $cd    = User::factory()->create(['role' => UserRole::CampusDirector]);

        $loa->markApprovedBy($dhDcs);
        $loa->markApprovedBy($saso);
        $this->assertTrue($loa->fresh()->campus_director_status->is(ApprovalStageStatus::Pending));

        $this->actingAs($cd)
            ->post(route('staff.loa.approve', $loa))
            ->assertRedirect(route('staff.pipeline'))
            ->assertSessionHas('status');

        $fresh = $loa->fresh();
        $this->assertTrue($fresh->campus_director_status->is(ApprovalStageStatus::Approved));
        $this->assertSame($cd->id, $fresh->campus_director_by);
        // CD is the final stage — status becomes 'approved'
        $this->assertSame('approved', $fresh->status);
        // Registrar and Guidance columns remain null (view-only, never auto-set)
        $this->assertNull($fresh->registrar_status);
        $this->assertNull($fresh->guidance_status);
    }

    /**
     * AC-6b: All 3 stages approved → overall status becomes 'approved'.
     */
    public function test_all_three_stages_approve_sets_overall_approved(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);

        $dh   = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $cd   = User::factory()->create(['role' => UserRole::CampusDirector]);

        $loa->markApprovedBy($dh);
        $this->assertSame('submitted', $loa->fresh()->status);
        $loa->markApprovedBy($saso);
        $this->assertSame('submitted', $loa->fresh()->status);
        $loa->markApprovedBy($cd);

        $fresh = $loa->fresh();
        $this->assertSame('approved', $fresh->status);
        $this->assertTrue($fresh->dept_head_status->is(ApprovalStageStatus::Approved));
        $this->assertTrue($fresh->saso_status->is(ApprovalStageStatus::Approved));
        $this->assertTrue($fresh->campus_director_status->is(ApprovalStageStatus::Approved));
    }

    /**
     * AC-8: Registrar and Guidance are view-only — they never see Approve/Reject buttons.
     */
    public function test_registrar_and_guidance_never_see_action_buttons(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);

        // Even after all 3 approving roles have acted, Registrar/Guidance still view-only
        $dh   = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $cd   = User::factory()->create(['role' => UserRole::CampusDirector]);
        $loa->markApprovedBy($dh);
        $loa->markApprovedBy($saso);
        $loa->markApprovedBy($cd);

        foreach ([UserRole::Registrar, UserRole::Guidance] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)
                ->get(route('staff.pipeline'))
                ->assertOk()
                ->assertSeeText('View')
                ->assertDontSeeText('Approve LOA')
                ->assertDontSeeText('Reject LOA');

            // Also blocked via HTTP POST
            $this->actingAs($user)
                ->post(route('staff.loa.approve', $loa))
                ->assertForbidden();
        }
    }

    /**
     * AC-7: Cross-dept DH POST approve → 403 from middleware (no DB change).
     */
    public function test_cross_dept_dh_post_approve_returns_403_and_no_db_change(): void
    {
        $dteLoa = $this->createSubmittedLoa($this->dte, $this->beed);

        $dcsDh = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dcsDh)
            ->post(route('staff.loa.approve', $dteLoa))
            ->assertForbidden();

        $fresh = $dteLoa->fresh();
        // Still pending, no changes
        $this->assertTrue($fresh->dept_head_status->is(ApprovalStageStatus::Pending));
        $this->assertNull($fresh->dept_head_by);
    }

    /**
     * Reports page skeleton sections present (AC-9).
     */
    public function test_reports_page_renders_filters_stats_charts(): void
    {
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $this->seedLoas(3, 2);

        $this->actingAs($saso)
            ->get(route('staff.reports'))
            ->assertOk()
            // Sidebar (staff shell)
            ->assertSeeText('Dashboard')
            ->assertSeeText('Approval Pipeline')
            ->assertSeeText('Reports & Analytics')
            ->assertSeeText('User Profile')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Request LOA')
            // Filter labels
            ->assertSeeText('Year')
            ->assertSeeText('Department')
            ->assertSeeText('Status')
            ->assertSeeText('Apply')
            // No "future release" text
            ->assertDontSeeText('future release')
            // Stat card headings
            ->assertSeeText('LOA Count')
            ->assertSeeText('Processing Time')
            ->assertSeeText('Highest Program')
            // Chart section labels
            ->assertSeeText('Volume by Department')
            ->assertSeeText('Monthly Submissions')
            ->assertSeeText('Status Distribution')
            ->assertSeeText('Top Programs by Submission Volume');
    }

    /**
     * Profile page shows basic user info.
     */
    public function test_profile_page_shows_user_info(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Maria',
            'middle_name' => null,
            'last_name' => 'Santos',
            'email' => 'maria@evsu.edu.ph',
            'role' => UserRole::Registrar,
        ]);

        $this->actingAs($user)
            ->get(route('staff.profile'))
            ->assertOk()
            ->assertSeeText('Maria Santos')
            ->assertSeeText('maria@evsu.edu.ph')
            ->assertSeeText('Registrar')
            ->assertSeeText('Edit name')
            ->assertDontSeeText('Change Password')
            ->assertDontSeeText('Your Activity')
            ->assertDontSeeText('Account actions coming soon')
            ->assertDontSeeText('future release');
    }

    /**
     * Double-approve prevented by policy (policy refuses second approve).
     */
    public function test_double_approve_prevented(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $dhDcs = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);

        // First approve → OK
        $this->actingAs($dhDcs)->post(route('staff.loa.approve', $loa))->assertRedirect();
        $this->assertTrue($loa->fresh()->dept_head_status->is(ApprovalStageStatus::Approved));

        // Second approve at same stage → 403
        $this->actingAs($dhDcs)->post(route('staff.loa.approve', $loa))->assertForbidden();
    }

    // ===============================
    // Helpers
    // ===============================

    private function createSubmittedLoa(Department $dept, Program $prog): LoaRequest
    {
        $token = LoaAccessToken::create([
            'student_id'   => fake()->numerify('####-####'),
            'full_name'    => fake()->name(),
            'email'        => fake()->safeEmail(),
            'token_hash'   => hash('sha256', Str::random(40)),
            'expires_at'   => now()->addHours(24),
            'otp_verified' => true, // pre-verified so resolveToken() guard passes
        ]);

        $loa = LoaRequest::create([
            'loa_access_token_id' => $token->id,
            'student_id'          => $token->student_id,
            'full_name'           => $token->full_name,
            'email'               => $token->email,
            'department_id'       => $dept->id,
            'program_id'          => $prog->id,
            'year_level'          => $prog->code . '-1st',
            'start_date'          => now()->addDays(5)->toDateString(),
            'return_date'         => now()->addDays(60)->toDateString(),
            'reason'              => fake()->paragraph(),
            'parent_full_name'    => fake()->name(),
            'parent_relationship' => 'Parent',
            'parent_phone'        => '+639171234567',
            'status'              => 'submitted',
            'submitted_at'        => now(),
            'dept_head_status'    => ApprovalStageStatus::Pending,
        ]);

        $loa->assignControlNumber();
        $loa->save();

        return $loa->fresh();
    }

    public function test_pdf_download_forbidden_when_not_approved(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->get(route('staff.loa.pdf', $loa))
            ->assertStatus(403);
    }

    public function test_pdf_download_forbidden_for_cross_department_dh(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $loa->forceFill(['status' => 'approved'])->save();

        $dteHead = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dte->id,
        ]);

        $this->actingAs($dteHead)
            ->get(route('staff.loa.pdf', $loa))
            ->assertStatus(403);
    }

    public function test_pdf_download_succeeds_for_approved_loa(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $loa->forceFill(['status' => 'approved'])->save();

        $dcsHead = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $response = $this->actingAs($dcsHead)
            ->get(route('staff.loa.pdf', $loa));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('LOA-' . $loa->control_number . '.pdf', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_loa_approved_mail_has_pdf_attachment(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $loa->forceFill(['status' => 'approved'])->save();

        $mailable = new \App\Mail\LoaApprovedMail($loa);
        $attachments = $mailable->attachments();

        $this->assertCount(1, $attachments);
        $this->assertSame('LOA-' . $loa->control_number . '.pdf', $attachments[0]->as);
        $this->assertSame('application/pdf', $attachments[0]->mime);
    }

    public function test_dh_reject_emails_student(): void
    {
        Mail::fake();

        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $dh = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);

        $this->actingAs($dh)
            ->post(route('staff.loa.reject', $loa), ['reason' => 'Incomplete documents.'])
            ->assertRedirect(route('staff.pipeline'));

        $this->assertSame('rejected', $loa->fresh()->status);

        Mail::assertSent(LoaRejectedMail::class, function ($mail) use ($loa) {
            return $mail->loa->id === $loa->id && $mail->hasTo($loa->email);
        });
    }

    public function test_saso_reject_emails_student(): void
    {
        Mail::fake();

        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $dh = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $loa->markApprovedBy($dh);

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->post(route('staff.loa.reject', $loa), ['reason' => 'SASO grounds for rejection.'])
            ->assertRedirect(route('staff.pipeline'));

        $this->assertSame('rejected', $loa->fresh()->status);

        Mail::assertSent(LoaRejectedMail::class, function ($mail) use ($loa) {
            return $mail->loa->id === $loa->id && $mail->hasTo($loa->email);
        });
    }

    public function test_campus_director_reject_emails_student(): void
    {
        Mail::fake();

        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $dh = User::factory()->create(['role' => UserRole::DepartmentHead, 'department_id' => $this->dcs->id]);
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $cd = User::factory()->create(['role' => UserRole::CampusDirector]);

        $loa->markApprovedBy($dh);
        $loa->markApprovedBy($saso);

        $this->assertTrue($loa->fresh()->campus_director_status->is(ApprovalStageStatus::Pending));

        $this->actingAs($cd)
            ->post(route('staff.loa.reject', $loa), ['reason' => 'Campus Director disapproved.'])
            ->assertRedirect(route('staff.pipeline'));

        $this->assertSame('rejected', $loa->fresh()->status);
        $this->assertTrue($loa->fresh()->campus_director_status->is(ApprovalStageStatus::Rejected));

        Mail::assertSent(LoaRejectedMail::class, function ($mail) use ($loa) {
            return $mail->loa->id === $loa->id && $mail->hasTo($loa->email);
        });
    }

    public function test_student_can_check_status_using_student_id_and_control_number(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);

        $this->post(route('student.status'), [
            'control_number' => $loa->control_number,
            'student_id'     => $loa->student_id,
        ])
        ->assertOk()
        ->assertSeeText($loa->control_number)
        ->assertSeeText($loa->full_name)
        ->assertDontSeeText('No application found.');
    }

    public function test_student_can_check_status_of_discontinued_loa(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);
        $loa->markDiscontinued($saso, 'Student withdrew request voluntarily.');

        $this->post(route('student.status'), [
            'control_number' => $loa->control_number,
            'student_id'     => $loa->student_id,
        ])
        ->assertOk()
        ->assertSeeText($loa->control_number)
        ->assertSeeText('Discontinued / Withdrawn')
        ->assertSeeText('Student withdrew request voluntarily.')
        ->assertDontSeeText('No application found.');
    }

    public function test_staff_can_discontinue_loa_and_email_student(): void
    {
        Mail::fake();

        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->post(route('staff.loa.discontinue', $loa), ['reason' => 'Student opted for study leave cancellation.'])
            ->assertRedirect(route('staff.loa.show', $loa))
            ->assertSessionHas('status');

        $fresh = $loa->fresh();
        $this->assertSame('discontinued', $fresh->status);
        $this->assertSame('Student opted for study leave cancellation.', $fresh->discontinuation_reason);
        $this->assertSame($saso->id, $fresh->discontinued_by);
        $this->assertNotNull($fresh->discontinued_at);

        Mail::assertSent(LoaDiscontinuedMail::class, function ($mail) use ($loa) {
            return $mail->loa->id === $loa->id
                && $mail->hasTo($loa->email)
                && $mail->statusUrl === url('/loa/status');
        });
    }

    public function test_cross_dept_dh_cannot_discontinue_loa(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $dteHead = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dte->id,
        ]);

        $this->actingAs($dteHead)
            ->post(route('staff.loa.discontinue', $loa), ['reason' => 'Test reason'])
            ->assertForbidden();

        $this->assertSame('submitted', $loa->fresh()->status);
    }

    public function test_cannot_discontinue_already_rejected_loa(): void
    {
        $loa = $this->createSubmittedLoa($this->dcs, $this->bsit);
        $loa->forceFill(['status' => 'rejected'])->save();

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->post(route('staff.loa.discontinue', $loa), ['reason' => 'Test reason'])
            ->assertForbidden();
    }

    private function seedLoas(int $dcsCount, int $dteCount): void
    {
        foreach (range(1, $dcsCount) as $i) {
            $this->createSubmittedLoa($this->dcs, $this->bsit);
        }
        foreach (range(1, $dteCount) as $i) {
            $this->createSubmittedLoa($this->dte, $this->beed);
        }
    }
}

