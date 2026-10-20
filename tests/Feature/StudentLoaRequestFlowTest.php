<?php

namespace Tests\Feature;

use App\Mail\LoaFormAccessMail;
use App\Mail\LoaOtpMail;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentLoaRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('Request LOA')->assertSee('Extend leave')->assertSee('Staff login');
    }

    public function test_identity_sends_otp_and_submission_creates_control_number(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->seed();

        $department = Department::query()->where('code', 'DCS')->firstOrFail();
        $program    = Program::query()->where('code', 'BSIT')->firstOrFail();

        // ── Step 1: submit email → redirects to verify/{token} ───────────────
        $response = $this->post('/loa/request', [
            'email' => 'juan@evsu.edu.ph',
        ]);

        Mail::assertSent(LoaOtpMail::class, function ($mail) {
            return $mail->email === 'juan@evsu.edu.ph';
        });

        // Extract token from redirect URL
        $redirectUrl = $response->headers->get('Location');
        $token = basename(parse_url($redirectUrl, PHP_URL_PATH));

        $response->assertRedirect(route('student.identity.verify', $token));

        // ── Step 2: show OTP page ─────────────────────────────────────────────
        $this->get(route('student.identity.verify', $token))
            ->assertOk()
            ->assertSee('Enter your code');

        // ── Step 3: submit wrong OTP → error ──────────────────────────────────
        $this->post(route('student.identity.verify.store', $token), ['otp' => '000000'])
            ->assertSessionHasErrors(['otp']);

        // ── Step 4: submit correct OTP → redirects to form ───────────────────
        $accessToken = LoaAccessToken::query()
            ->where('token_hash', LoaAccessToken::hashPlainToken($token))
            ->firstOrFail();

        // Get the real OTP by re-hashing — we need the plain OTP from the model
        // In tests we bypass email and grab it via a fresh token issue
        $plainOtp = null;
        // Patch: set a known OTP directly on the record for testing
        $knownOtp = '123456';
        $accessToken->update([
            'otp_hash'       => hash('sha256', $knownOtp),
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        $this->post(route('student.identity.verify.store', $token), ['otp' => $knownOtp])
            ->assertRedirect(route('student.identity.sent'));

        // Form link email should now be sent
        Mail::assertSent(LoaFormAccessMail::class, function ($mail) {
            return $mail->to[0]['address'] === 'juan@evsu.edu.ph';
        });

        // ── Step 5: student opens email link to access the form ───────────────
        // (In real usage the form URL comes from the email; in tests we use the token directly)
        $this->get(route('student.form.show', $token))
            ->assertOk()
            ->assertSee('EVSU-SASO-F-040');

        // ── Step 6: duplicate email blocked after OTP verified ────────────────
        $this->post('/loa/request', [
            'email' => 'juan@evsu.edu.ph',
        ])->assertSessionHasErrors(['email']);

        // ── Step 7: submit the form ───────────────────────────────────────────
        $this->post(route('student.form.store', $token), [
            'student_id'          => '2021-0001',
            'first_name'          => 'Juan',
            'middle_name'         => 'Dela',
            'last_name'           => 'Cruz',
            'department_id'       => $department->id,
            'program_id'          => $program->id,
            'year_level'          => 'BSIT-1st',
            'academic_year'       => '2026-2027',
            'semester'            => 'first',
            'start_date'          => '2026-10-01',
            'return_date'         => '2027-03-01',
            'reason'              => 'Medical treatment requiring extended recovery.',
            'parent_first_name'   => 'Maria',
            'parent_middle_name'  => '',
            'parent_last_name'    => 'Dela Cruz',
            'parent_relationship' => 'mother',
            'parent_phone'        => '917-123-4567',
            'attachment_categories' => ['medical', 'valid_id'],
            'attachments'         => [
                UploadedFile::fake()->create('medical.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->image('id.jpg'),
            ],
        ])->assertRedirect(route('student.form.submitted', $token));

        $this->assertDatabaseHas('loa_requests', [
            'student_id'             => '2021-0001',
            'first_name'             => 'Juan',
            'last_name'              => 'Cruz',
            'full_name'              => 'Juan Dela Cruz',
            'parent_first_name'      => 'Maria',
            'parent_last_name'       => 'Dela Cruz',
            'parent_full_name'       => 'Maria Dela Cruz',
            'email'                  => 'juan@evsu.edu.ph',
            'status'                 => 'submitted',
            'department_id'          => $department->id,
            'program_id'             => $program->id,
            'academic_year'          => '2026-2027',
            'semester'               => 'first',
            'dept_head_status'       => 'pending',
            'saso_status'            => null,
            'campus_director_status' => null,
        ]);

        $loa = LoaRequest::query()->first();
        $this->assertSame(2, $loa->attachments()->count());
        $this->assertStringStartsWith('EVSU-OC-LOA-2026-', $loa->control_number);
    }

    public function test_form_blocks_duplicate_student_id_with_active_or_approved_leave(): void
    {
        Mail::fake();
        $this->seed();

        $department = Department::query()->where('code', 'DCS')->firstOrFail();
        $program    = Program::query()->where('code', 'BSIT')->firstOrFail();

        // Existing submitted LOA with student_id 2021-9999
        $existingToken = LoaAccessToken::create([
            'student_id'    => '2021-9999',
            'full_name'     => 'Existing Student',
            'email'         => 'existing@evsu.edu.ph',
            'token_hash'    => LoaAccessToken::hashPlainToken(\Illuminate\Support\Str::random(40)),
            'expires_at'    => now()->addHours(24),
            'otp_verified'  => true,
            'used_at'       => now(),
        ]);

        LoaRequest::create([
            'loa_access_token_id' => $existingToken->id,
            'control_number' => 'EVSU-OC-LOA-2026-9999',
            'student_id'     => '2021-9999',
            'first_name'     => 'Existing',
            'last_name'      => 'Student',
            'full_name'      => 'Existing Student',
            'email'          => 'existing@evsu.edu.ph',
            'status'         => 'submitted',
            'department_id'  => $department->id,
            'program_id'     => $program->id,
        ]);

        // Second student requests token with a different email
        [$tokenRecord, $plainToken] = LoaAccessToken::issueForEmail('other@evsu.edu.ph', [], '127.0.0.1');
        $tokenRecord->update(['otp_verified' => true]);

        // Student opens the form link (creates draft)
        $this->get(route('student.form.show', $plainToken))->assertOk();

        // Attempt to submit the form using 2021-9999
        $this->post(route('student.form.store', $plainToken), [
            'student_id'          => '2021-9999',
            'first_name'          => 'New',
            'last_name'           => 'User',
            'department_id'       => $department->id,
            'program_id'          => $program->id,
            'year_level'          => 'BSIT-1st',
            'academic_year'       => '2026-2027',
            'semester'            => 'first',
            'start_date'          => '2026-10-01',
            'return_date'         => '2027-03-01',
            'reason'              => 'Some valid reason.',
            'parent_first_name'   => 'Parent',
            'parent_last_name'    => 'User',
            'parent_relationship' => 'father',
            'parent_phone'        => '917-111-2222',
        ])->assertSessionHasErrors(['student_id']);
    }

    public function test_complete_lifecycle_from_request_to_approval_and_extension_prefill(): void
    {
        Mail::fake();
        Storage::fake('local');
        $this->seed();

        $department = Department::query()->where('code', 'DCS')->firstOrFail();
        $program    = Program::query()->where('code', 'BSIT')->firstOrFail();

        $dh = \App\Models\User::query()->where('email', 'dcs.head@evsu.edu.ph')->firstOrFail();
        $saso = \App\Models\User::query()->where('email', 'saso@evsu.edu.ph')->firstOrFail();
        $cd = \App\Models\User::query()->where('email', 'director@evsu.edu.ph')->firstOrFail();

        // ── 1. Student requests OTP with email only ───────────────────────────
        $this->post('/loa/request', [
            'email' => 'student@evsu.edu.ph',
        ])->assertRedirect();

        Mail::assertSent(LoaOtpMail::class);

        $tokenRecord = LoaAccessToken::query()->where('email', 'student@evsu.edu.ph')->firstOrFail();
        $this->assertSame('', $tokenRecord->student_id);
        $this->assertSame('', $tokenRecord->full_name);

        // ── 2. Student verifies OTP ───────────────────────────────────────────
        $knownOtp = '654321';
        $tokenRecord->update([
            'otp_hash'       => hash('sha256', $knownOtp),
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        $plainToken = \Illuminate\Support\Str::random(40);
        $tokenRecord->update([
            'token_hash' => LoaAccessToken::hashPlainToken($plainToken),
        ]);

        $this->post(route('student.identity.verify.store', $plainToken), [
            'otp' => $knownOtp,
        ])->assertRedirect(route('student.identity.sent'));

        // ── 3. Student tries to request again with same email → blocked ───────
        $this->post('/loa/request', [
            'email' => 'student@evsu.edu.ph',
        ])->assertSessionHasErrors(['email']);

        // ── 4. Student opens form → sees form and inputs name/ID manually ─────
        $response = $this->get(route('student.form.show', $plainToken));
        $response->assertOk();
        $response->assertSee('Student number');
        $response->assertSee('First name');
        $response->assertSee('Last name');

        // ── 5. Student submits form with their details ────────────────────────
        $this->post(route('student.form.store', $plainToken), [
            'student_id'          => '2022-12345',
            'first_name'          => 'Maria',
            'middle_name'         => 'Santos',
            'last_name'           => 'Dela Cruz',
            'department_id'       => $department->id,
            'program_id'          => $program->id,
            'year_level'          => 'BSIT-1st',
            'academic_year'       => '2026-2027',
            'semester'            => 'first',
            'start_date'          => '2026-11-01',
            'return_date'         => '2027-04-01',
            'reason'              => 'Need to assist family in urgent personal matter.',
            'parent_first_name'   => 'Juan',
            'parent_middle_name'  => 'Perez',
            'parent_last_name'    => 'Dela Cruz',
            'parent_relationship' => 'father',
            'parent_phone'        => '917-555-4321',
        ])->assertRedirect(route('student.form.submitted', $plainToken));

        $loa = LoaRequest::query()->where('student_id', '2022-12345')->firstOrFail();
        $this->assertSame('Maria Santos Dela Cruz', $loa->full_name);
        $this->assertSame('Juan Perez Dela Cruz', $loa->parent_full_name);
        $this->assertSame('submitted', $loa->status);

        // Access token is updated with student_id and full_name
        $tokenRecord->refresh();
        $this->assertSame('2022-12345', $tokenRecord->student_id);
        $this->assertSame('Maria Santos Dela Cruz', $tokenRecord->full_name);

        // ── 6. Student opens the form URL again → redirects to submitted ─────
        $this->get(route('student.form.show', $plainToken))
            ->assertRedirect(route('student.form.submitted', $plainToken));

        // ── 7. Public status check works with control number and student ID ───
        $this->post(route('student.status'), [
            'control_number' => $loa->control_number,
            'student_id'     => '2022-12345',
        ])->assertOk()->assertSee($loa->control_number)->assertSee('Pending: Dept Head');

        // ── 8. Department Head approves ───────────────────────────────────────
        $this->actingAs($dh)->post(route('staff.loa.approve', $loa))->assertRedirect();
        $loa->refresh();
        $this->assertSame('approved', $loa->dept_head_status?->value);
        $this->assertSame('pending', $loa->saso_status?->value);

        // ── 9. SASO Officer approves ──────────────────────────────────────────
        $this->actingAs($saso)->post(route('staff.loa.approve', $loa))->assertRedirect();
        $loa->refresh();
        $this->assertSame('approved', $loa->saso_status?->value);
        $this->assertSame('pending', $loa->campus_director_status?->value);

        // ── 10. Campus Director approves → Fully Approved & PDF ready ─────────
        $this->actingAs($cd)->post(route('staff.loa.approve', $loa))->assertRedirect();
        $loa->refresh();
        $this->assertSame('approved', $loa->status);

        // PDF download succeeds for approved LOA
        $this->actingAs($cd)->get(route('staff.loa.pdf', $loa))->assertOk();

        // ── 11. Student tries to request another new leave → blocked ──────────
        $this->post('/loa/request', [
            'email' => 'student@evsu.edu.ph',
        ])->assertSessionHasErrors(['email']);

        // ── 12. Student requests extension at /loa/extend ─────────────────────
        $this->post(route('student.extend.store'), [
            'student_id' => '2022-12345',
            'email'      => 'student@evsu.edu.ph',
        ])->assertRedirect();

        $extToken = LoaAccessToken::query()
            ->where('purpose', 'extension')
            ->where('student_id', '2022-12345')
            ->firstOrFail();

        $this->assertSame($loa->id, $extToken->parent_loa_id);
        $this->assertSame('Maria', $extToken->first_name);
        $this->assertSame('Dela Cruz', $extToken->last_name);

        // Verify OTP for extension
        $extOtp = '987654';
        $extPlain = \Illuminate\Support\Str::random(40);
        $extToken->update([
            'otp_hash'       => hash('sha256', $extOtp),
            'otp_expires_at' => now()->addMinutes(5),
            'token_hash'     => LoaAccessToken::hashPlainToken($extPlain),
        ]);

        $this->post(route('student.identity.verify.store', $extPlain), ['otp' => $extOtp])
            ->assertRedirect(route('student.identity.sent'));

        // ── 13. Student opens extension form → pre-filled and locked ──────────
        $extFormResponse = $this->get(route('student.form.show', $extPlain));
        $extFormResponse->assertOk();
        $extFormResponse->assertSee('From your approved leave');
        $extFormResponse->assertSee('2022-12345');
        $extFormResponse->assertSee('Maria Santos Dela Cruz');
    }

    public function test_student_request_goback_resend_editable_fields_and_approval_flow(): void
    {
        Mail::fake();
        Storage::fake('local');
        $this->seed();

        $dcsDept = Department::query()->where('code', 'DCS')->firstOrFail();
        $dteDept = Department::query()->where('code', 'DTE')->firstOrFail();
        $program = Program::query()->where('code', 'BSIT')->firstOrFail();

        $dhDcs = \App\Models\User::query()->where('email', 'dcs.head@evsu.edu.ph')->firstOrFail();
        $dhDte = \App\Models\User::query()->where('email', 'dte.head@evsu.edu.ph')->firstOrFail();
        $saso  = \App\Models\User::query()->where('email', 'saso@evsu.edu.ph')->firstOrFail();
        $cd    = \App\Models\User::query()->where('email', 'director@evsu.edu.ph')->firstOrFail();

        // 1. Non-EVSU email is rejected
        $this->post('/loa/request', ['email' => 'student@gmail.com'])
            ->assertSessionHasErrors(['email']);

        // 2. Valid EVSU email receives OTP
        $this->post('/loa/request', ['email' => 'student.flow@evsu.edu.ph'])
            ->assertRedirect();
        Mail::assertSent(LoaOtpMail::class);

        // 3. User goes back / starts over before verifying OTP, and uses an updated email
        $this->get('/loa/request')->assertOk();
        $this->post('/loa/request', ['email' => 'student.flow2@evsu.edu.ph'])
            ->assertRedirect();

        $tokenRecord = LoaAccessToken::query()
            ->where('email', 'student.flow2@evsu.edu.ph')
            ->firstOrFail();

        // 4. Verify OTP page loads and bad OTP fails
        $plainToken = \Illuminate\Support\Str::random(40);
        $knownOtp   = '543210';
        $tokenRecord->update([
            'token_hash'     => LoaAccessToken::hashPlainToken($plainToken),
            'otp_hash'       => hash('sha256', $knownOtp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->get(route('student.identity.verify', $plainToken))
            ->assertOk()
            ->assertSee('Enter your code')
            ->assertSee('Start over');

        $this->post(route('student.identity.verify.store', $plainToken), ['otp' => '000000'])
            ->assertSessionHasErrors(['otp']);

        // 5. Submit valid OTP -> link sent and redirects to sent confirmation
        $this->post(route('student.identity.verify.store', $plainToken), ['otp' => $knownOtp])
            ->assertRedirect(route('student.identity.sent'));

        Mail::assertSent(LoaFormAccessMail::class, function ($mail) {
            return $mail->to[0]['address'] === 'student.flow2@evsu.edu.ph';
        });

        // 6. Check sent page
        $this->withSession(['email' => 'student.flow2@evsu.edu.ph'])
            ->get(route('student.identity.sent'))
            ->assertOk()
            ->assertSee('Check your email');

        // 7. Re-requesting with same email while token is active is blocked
        $this->post('/loa/request', ['email' => 'student.flow2@evsu.edu.ph'])
            ->assertSessionHasErrors(['email']);

        // 8. Open form link -> form renders editable inputs without disabled/readonly
        $formRes = $this->get(route('student.form.show', $plainToken));
        $formRes->assertOk();
        $formRes->assertDontSee('From your approved leave');
        $formRes->assertSee('id="student_id"', false);
        $formRes->assertSee('id="first_name"', false);
        $formRes->assertSee('id="middle_name"', false);
        $formRes->assertSee('id="last_name"', false);

        // 9. Validation: student_id format and required names
        $this->post(route('student.form.store', $plainToken), [
            'student_id' => 'not-valid-id',
            'first_name' => '',
            'last_name'  => '',
        ])->assertSessionHasErrors(['student_id', 'first_name', 'last_name']);

        // 10. Fill and submit form with editable inputs
        $this->post(route('student.form.store', $plainToken), [
            'student_id'          => '2024-55555',
            'first_name'          => 'Juan',
            'middle_name'         => 'Rizal',
            'last_name'           => 'Mercado',
            'department_id'       => $dcsDept->id,
            'program_id'          => $program->id,
            'year_level'          => 'BSIT-2nd',
            'academic_year'       => '2026-2027',
            'semester'            => 'first',
            'start_date'          => '2026-11-01',
            'return_date'         => '2027-04-01',
            'reason'              => 'Family responsibility requiring relocation.',
            'parent_first_name'   => 'Francisco',
            'parent_middle_name'  => 'Mercado',
            'parent_last_name'    => 'Rizal',
            'parent_relationship' => 'father',
            'parent_phone'        => '917-123-4567',
        ])->assertRedirect(route('student.form.submitted', $plainToken));

        $loa = LoaRequest::query()->where('student_id', '2024-55555')->firstOrFail();
        $this->assertSame('Juan Rizal Mercado', $loa->full_name);
        $this->assertSame('submitted', $loa->status);

        // Token backfilled
        $tokenRecord->refresh();
        $this->assertSame('2024-55555', $tokenRecord->student_id);
        $this->assertSame('Juan Rizal Mercado', $tokenRecord->full_name);

        // 11. Re-opening the form redirects to submitted page
        $this->get(route('student.form.show', $plainToken))
            ->assertRedirect(route('student.form.submitted', $plainToken));

        // 12. Cross-department head (DTE) cannot approve DCS LOA
        $this->actingAs($dhDte)
            ->post(route('staff.loa.approve', $loa))
            ->assertForbidden();

        // 13. Sequential approvals: DH DCS -> SASO -> Campus Director
        $this->actingAs($dhDcs)->post(route('staff.loa.approve', $loa))->assertRedirect();
        $loa->refresh();
        $this->assertSame('approved', $loa->dept_head_status?->value);

        $this->actingAs($saso)->post(route('staff.loa.approve', $loa))->assertRedirect();
        $loa->refresh();
        $this->assertSame('approved', $loa->saso_status?->value);

        $this->actingAs($cd)->post(route('staff.loa.approve', $loa))->assertRedirect();
        $loa->refresh();
        $this->assertSame('approved', $loa->status);

        // 14. PDF generation succeeds for fully approved LOA
        $this->actingAs($cd)->get(route('staff.loa.pdf', $loa))->assertOk();

        // 15. Status tracking with student_id and control number
        $this->post(route('student.status'), [
            'control_number' => $loa->control_number,
            'student_id'     => '2024-55555',
        ])->assertOk()->assertSee('Fully Approved');

        // 16. New leave request with this email is blocked
        $this->post('/loa/request', ['email' => 'student.flow2@evsu.edu.ph'])
            ->assertSessionHasErrors(['email']);

        // 17. Extension retains student_id and locks pre-filled identity
        $this->post(route('student.extend.store'), [
            'student_id' => '2024-55555',
            'email'      => 'student.flow2@evsu.edu.ph',
        ])->assertRedirect();

        $extToken = LoaAccessToken::query()
            ->where('purpose', 'extension')
            ->where('student_id', '2024-55555')
            ->firstOrFail();

        $extPlain = \Illuminate\Support\Str::random(40);
        $extOtp   = '112233';
        $extToken->update([
            'token_hash'     => LoaAccessToken::hashPlainToken($extPlain),
            'otp_hash'       => hash('sha256', $extOtp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->post(route('student.identity.verify.store', $extPlain), ['otp' => $extOtp])
            ->assertRedirect(route('student.identity.sent'));

        $extFormRes = $this->get(route('student.form.show', $extPlain));
        $extFormRes->assertOk();
        $extFormRes->assertSee('From your approved leave');
        $extFormRes->assertSee('2024-55555');
        $extFormRes->assertSee('Juan Rizal Mercado');
        $extFormRes->assertSee('type="hidden" name="student_id" value="2024-55555"', false);
    }
}
