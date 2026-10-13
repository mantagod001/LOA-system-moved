<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Check if the account exists but hasn't been activated via invitation yet.
        $pending = \App\Models\User::query()
            ->where('email', strtolower($credentials['email']))
            ->whereNull('invitation_accepted_at')
            ->exists();

        if ($pending) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is not activated yet. Check your email for the setup link.']);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Those credentials do not match our records.']);
        }

        $request->session()->regenerate();

        $home = auth()->user()->role->is(\App\Enums\UserRole::Administrator)
            ? route('staff.profile')
            : route('staff.dashboard');

        return redirect()->intended($home);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
