<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\StaffPasswordOtpMail;
use App\Mail\StaffPasswordResetMail;
use App\Models\StaffPasswordReset;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower($validated['email']);
        [$reset, $plainToken, $plainOtp] = StaffPasswordReset::issue($email, $request->ip());

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNotNull('invitation_accepted_at')
            ->first();

        if ($user) {
            try {
                Mail::to($user->email)->send(new StaffPasswordOtpMail($plainOtp, $user->email));
            } catch (\Throwable $e) {
                report($e);
                $reset->delete();

                return back()
                    ->withInput()
                    ->withErrors(['email' => 'We could not send the verification code right now. Please try again in a moment.']);
            }
        }

        return redirect()
            ->route('password.verify', ['token' => $plainToken])
            ->with('otp_email', $email);
    }

    public function showVerify(string $token): View|RedirectResponse
    {
        $reset = StaffPasswordReset::findOpenRequest($token);

        if (! $reset) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'That reset request is invalid or has expired. Please start again.']);
        }

        if ($reset->otp_verified_at) {
            return redirect()->route('password.sent');
        }

        return view('auth.forgot-password-verify', [
            'token' => $token,
            'email' => $reset->email,
        ]);
    }

    public function verify(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit code from your email.',
            'otp.digits'   => 'The code must be exactly 6 digits.',
        ]);

        $reset = StaffPasswordReset::findOpenRequest($token);

        if (! $reset) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'That reset request is invalid or has expired. Please start again.']);
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$reset->email])
            ->whereNotNull('invitation_accepted_at')
            ->first();

        if (! $user || ! $reset->verifyOtp($request->input('otp'))) {
            return back()
                ->withErrors(['otp' => 'The code is incorrect or has expired. Check your email and try again.']);
        }

        $plainResetToken = $reset->issueResetToken();
        $resetUrl = url('/staff/reset-password/'.$plainResetToken);

        try {
            Mail::to($user->email)->send(new StaffPasswordResetMail($resetUrl, $user->email));
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['otp' => 'Email verified but we could not send the reset link. Please try again.']);
        }

        return redirect()->route('password.sent');
    }

    public function sent(): View
    {
        return view('auth.forgot-password-sent');
    }

    public function showReset(string $token): View|RedirectResponse
    {
        $reset = StaffPasswordReset::findOpenReset($token);

        if (! $reset) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'That password reset link is invalid or has expired. Please start again.']);
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request, string $token): RedirectResponse
    {
        $reset = StaffPasswordReset::findOpenReset($token);

        if (! $reset) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'That password reset link is invalid or has expired. Please start again.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$reset->email])
            ->whereNotNull('invitation_accepted_at')
            ->first();

        if (! $user) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'That password reset link is invalid or has expired. Please start again.']);
        }

        $user->forceFill([
            'password' => $request->input('password'),
            'remember_token' => Str::random(60),
        ])->save();

        $reset->update(['used_at' => now()]);

        DB::table('sessions')->where('user_id', $user->id)->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Your password has been reset. You can log in with the new password.');
    }
}
