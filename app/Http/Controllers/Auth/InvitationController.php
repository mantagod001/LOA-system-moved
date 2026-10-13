<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $user = $this->resolveToken($token);

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'That invitation link is invalid or has already been used.']);
        }

        return view('auth.invite', compact('token', 'user'));
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $user = $this->resolveToken($token);

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'That invitation link is invalid or has already been used.']);
        }

        $request->validate([
            'first_name'  => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'password'    => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->forceFill([
            'first_name'              => $request->input('first_name'),
            'middle_name'             => $request->input('middle_name'),
            'last_name'               => $request->input('last_name'),
            'password'                => $request->input('password'),
            'invitation_token'        => null,
            'invitation_accepted_at'  => now(),
            'email_verified_at'       => now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->role->is(\App\Enums\UserRole::Administrator) ? 'staff.profile' : 'staff.dashboard')
            ->with('status', 'Welcome to LeaveFlow! Your account is now active.');
    }

    private function resolveToken(string $token): ?User
    {
        if (! preg_match('/^[A-Za-z0-9]{48}$/', $token)) {
            return null;
        }

        $user = User::findByInvitationToken($token);

        if (! $user) {
            return null;
        }

        // Treat the token as expired after 48 hours from when it was sent.
        if ($user->invitation_sent_at && $user->invitation_sent_at->lt(now()->subHours(48))) {
            return null;
        }

        return $user;
    }
}
