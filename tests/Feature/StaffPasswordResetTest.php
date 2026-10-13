<?php

namespace Tests\Feature;

use App\Mail\StaffPasswordOtpMail;
use App\Mail\StaffPasswordResetMail;
use App\Models\StaffPasswordReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StaffPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_forgot_password_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Forgot password?');
    }

    public function test_unknown_email_does_not_send_otp_but_still_shows_verify_page(): void
    {
        Mail::fake();

        $this->post(route('password.request.store'), [
            'email' => 'nobody@evsu.edu.ph',
        ])->assertRedirect();

        Mail::assertNothingSent();
        $this->assertDatabaseHas('staff_password_resets', [
            'email' => 'nobody@evsu.edu.ph',
        ]);
    }

    public function test_pending_invite_does_not_receive_otp(): void
    {
        Mail::fake();

        User::factory()->create([
            'email' => 'pending@evsu.edu.ph',
            'invitation_accepted_at' => null,
        ]);

        $this->post(route('password.request.store'), [
            'email' => 'pending@evsu.edu.ph',
        ])->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_activated_staff_resets_password_with_otp_then_link(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'saso@evsu.edu.ph',
            'password' => 'old-password-123',
        ]);

        $response = $this->post(route('password.request.store'), [
            'email' => 'saso@evsu.edu.ph',
        ]);

        Mail::assertSent(StaffPasswordOtpMail::class, function ($mail) {
            return $mail->email === 'saso@evsu.edu.ph';
        });

        $token = basename(parse_url($response->headers->get('Location'), PHP_URL_PATH));
        $response->assertRedirect(route('password.verify', $token));

        $reset = StaffPasswordReset::query()
            ->where('request_token_hash', StaffPasswordReset::hashPlain($token))
            ->firstOrFail();

        $knownOtp = '654321';
        $reset->update([
            'otp_hash' => StaffPasswordReset::hashPlain($knownOtp),
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        $this->post(route('password.verify.store', $token), ['otp' => '000000'])
            ->assertSessionHasErrors(['otp']);

        $this->post(route('password.verify.store', $token), ['otp' => $knownOtp])
            ->assertRedirect(route('password.sent'));

        Mail::assertSent(StaffPasswordResetMail::class);

        $mail = Mail::sent(StaffPasswordResetMail::class)->first();
        $resetToken = basename(parse_url($mail->resetUrl, PHP_URL_PATH));

        $this->get(route('password.reset', $resetToken))
            ->assertOk()
            ->assertSee('Set a new password');

        $this->post(route('password.reset.store', $resetToken), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertFalse(Hash::check('old-password-123', $user->fresh()->password));

        $this->post(route('login.store'), [
            'email' => 'saso@evsu.edu.ph',
            'password' => 'new-password-123',
        ])->assertRedirect(route('staff.dashboard'));
    }
}
