<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Mail\LoaFormAccessMail;
use App\Mail\LoaOtpMail;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class IdentityController extends Controller
{
    // ── Step 1: show email input ──────────────────────────────────────────────

    public function create(): View
    {
        return view('student.identity', [
            'isExtension' => request()->routeIs('student.extend'),
        ]);
    }

    // ── Step 2: validate email, send OTP ─────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $isExtension = $request->routeIs('student.extend.store');

        if ($isExtension) {
            $validated = $request->validate([
                'student_id' => ['required', 'string', 'regex:/^\d{4}-\d{4,6}$/'],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! str_ends_with(strtolower((string) $value), '@evsu.edu.ph')) {
                            $fail('Only official EVSU email addresses ending in @evsu.edu.ph are accepted.');
                        }
                    },
                ],
            ], [
                'student_id.regex' => 'Student ID must follow the format YYYY-NNNNN, e.g. 2021-12345.',
                'email.required' => 'Please enter your EVSU email address.',
                'email.email'    => 'Enter a valid email address.',
            ]);

            $email = strtolower($validated['email']);
            $parent = LoaRequest::approvedMatchingIdAndEmail($validated['student_id'], $email);
            $block = $this->extensionBlockReason($validated['student_id'], $email, $parent);
            if ($block) {
                return back()->withInput()->withErrors([
                    'email' => $block,
                    'student_id' => $block,
                ]);
            }

            [$accessToken, $plainToken, $plainOtp] = LoaAccessToken::issueForEmail($email, [
                'student_id'  => $parent?->student_id ?? $validated['student_id'],
                'first_name'  => $parent?->first_name ?? '',
                'middle_name' => $parent?->middle_name ?? null,
                'last_name'   => $parent?->last_name ?? '',
            ], $request->ip(), [
                'purpose' => 'extension',
                'parent_loa_id' => $parent?->id,
            ]);
        } else {
            $validated = $request->validate([
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! str_ends_with(strtolower((string) $value), '@evsu.edu.ph')) {
                            $fail('Only official EVSU email addresses ending in @evsu.edu.ph are accepted.');
                        }
                    },
                ],
            ], [
                'email.required' => 'Please enter your EVSU email address.',
                'email.email'    => 'Enter a valid email address.',
            ]);

            $email = strtolower($validated['email']);
            $message = $this->newLeaveEmailBlockReason($email);
            if ($message) {
                return back()
                    ->withInput()
                    ->withErrors(['email' => $message]);
            }

            [$accessToken, $plainToken, $plainOtp] = LoaAccessToken::issueForEmail($email, [], $request->ip(), [
                'purpose' => 'leave',
            ]);
        }

        try {
            Mail::to($email)->send(new LoaOtpMail($plainOtp, $email));
        } catch (\Throwable $e) {
            report($e);
            // Clean up the token since email failed
            $accessToken->delete();

            return back()
                ->withInput()
                ->withErrors(['email' => 'We could not send the verification code right now. Please try again in a moment.']);
        }

        return redirect()
            ->route('student.identity.verify', ['token' => $plainToken])
            ->with('otp_email', $email);
    }

    // ── Step 3: show OTP entry form ───────────────────────────────────────────

    public function showVerify(Request $request, string $token): View|RedirectResponse
    {
        $accessToken = $this->resolveToken($token);

        if (! $accessToken) {
            return $this->expiredIdentityRedirect($token);
        }

        // Already verified — go to check-email (form link was already sent)
        if ($accessToken->otp_verified) {
            return redirect()
                ->route('student.identity.sent')
                ->with('email', $accessToken->email)
                ->with('form_purpose', $accessToken->purpose);
        }

        return view('student.verify-otp', [
            'token' => $token,
            'email' => $accessToken->email,
            'isExtension' => $accessToken->isExtension(),
        ]);
    }

    // ── Step 4: verify OTP, send form link email, show check-email page ──────

    public function verify(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit code from your email.',
            'otp.digits'   => 'The code must be exactly 6 digits.',
        ]);

        $accessToken = $this->resolveToken($token);

        if (! $accessToken) {
            return $this->expiredIdentityRedirect($token);
        }

        if ($accessToken->otp_verified) {
            // Already verified — send them to check-email (form link was already sent)
            return redirect()
                ->route('student.identity.sent')
                ->with('email', $accessToken->email)
                ->with('form_purpose', $accessToken->purpose);
        }

        if (! $accessToken->verifyOtp($request->input('otp'))) {
            return back()
                ->withErrors(['otp' => 'The code is incorrect or has expired. Check your email and try again.']);
        }

        // OTP correct — send the form link to their email
        $formUrl = url('/loa/form/' . $token);

        try {
            Mail::to($accessToken->email)->send(new LoaFormAccessMail($accessToken, $formUrl));
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withErrors(['otp' => 'Email verified but we could not send your form link. Please try again.']);
        }

        // Show "check your email" page — form URL is only in the email
        return redirect()
            ->route('student.identity.sent')
            ->with('email', $accessToken->email)
            ->with('form_purpose', $accessToken->purpose);
    }

    // ── "Check your email" page — shown after OTP is verified and form link is sent ──

    public function sent(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('email')) {
            return redirect()->route('student.identity');
        }

        return view('student.check-email', [
            'isExtension' => $request->session()->get('form_purpose') === 'extension',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveToken(string $plainToken): ?LoaAccessToken
    {
        $hash = LoaAccessToken::hashPlainToken($plainToken);

        return LoaAccessToken::query()
            ->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();
    }

    private function newLeaveEmailBlockReason(string $email): ?string
    {
        $openLink = LoaAccessToken::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where('otp_verified', true)
            ->exists();

        $inReview = LoaRequest::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNotIn('status', ['rejected', 'discontinued', 'approved'])
            ->where(function ($q) {
                $q->where('status', '!=', 'draft')
                    ->orWhereHas('accessToken', function ($token) {
                        $token->where('expires_at', '>', now())->whereNull('used_at');
                    });
            })
            ->exists();

        if ($openLink || $inReview) {
            return 'This EVSU email already has an active LOA request. Only one request is allowed at a time.';
        }

        if (LoaRequest::query()->whereRaw('LOWER(email) = ?', [$email])->where('status', 'approved')->exists()) {
            return 'You already have an approved leave. Use Extend leave if you need more time.';
        }

        return null;
    }

    private function extensionBlockReason(string $studentId, string $email, ?LoaRequest $parent): ?string
    {
        if (LoaRequest::hasInFlightExtension($studentId, $email)) {
            return 'You already have a leave extension in progress. Only one extension request is allowed at a time.';
        }

        if (! $parent) {
            return 'No approved leave was found that matches both this student number and EVSU email.';
        }

        $openLink = LoaAccessToken::query()
            ->where('student_id', $studentId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('purpose', 'extension')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where('otp_verified', true)
            ->exists();

        if ($openLink) {
            return 'You already have a leave extension in progress. Only one extension request is allowed at a time.';
        }

        return null;
    }

    private function expiredIdentityRedirect(?string $token = null): RedirectResponse
    {
        $isExtension = false;

        if (filled($token)) {
            $access = LoaAccessToken::query()
                ->where('token_hash', LoaAccessToken::hashPlainToken($token))
                ->first();
            $isExtension = $access?->isExtension() ?? false;
        }

        return redirect()
            ->route($isExtension ? 'student.extend' : 'student.identity')
            ->withErrors(['email' => 'This verification link is invalid or has expired. Please start again.']);
    }
}
