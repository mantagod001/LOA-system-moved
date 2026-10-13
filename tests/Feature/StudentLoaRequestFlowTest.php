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
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
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
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
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
}
