<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Mail\StaffInvitationMail;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('department')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles'       => UserRole::cases(),
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = UserRole::tryFrom((string) $request->input('role'));

        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'middle_name'   => ['nullable', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'          => ['required', Rule::in(array_map(fn (UserRole $r) => $r->value, UserRole::cases()))],
            'department_id' => [
                Rule::requiredIf($role?->needsDepartment() ?? false),
                'nullable',
                'exists:departments,id',
            ],
        ]);

        $user = User::create([
            'first_name'    => $validated['first_name'],
            'middle_name'   => $validated['middle_name'] ?? null,
            'last_name'     => $validated['last_name'],
            'email'         => $validated['email'],
            'password'      => bcrypt(\Illuminate\Support\Str::random(32)),
            'role'          => $validated['role'],
            'department_id' => $role?->needsDepartment() ? $validated['department_id'] : null,
        ]);

        try {
            $this->sendInvitation($user);
        } catch (\Throwable $e) {
            report($e);
            return redirect()
                ->route('admin.users.index')
                ->with('status', "User {$user->email} created but invitation email failed: {$e->getMessage()}. Use Resend to try again.");
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Invitation sent to {$user->email}.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'invitee'     => $user,
            'roles'       => UserRole::cases(),
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        // Prevent editing your own role or the last Administrator
        if ($user->id === auth()->id()) {
            return back()->withErrors(['role' => 'You cannot edit your own account here.']);
        }

        $role = UserRole::tryFrom((string) $request->input('role'));

        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:100'],
            'middle_name'   => ['nullable', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
            'role'          => ['required', Rule::in(array_map(fn (UserRole $r) => $r->value, UserRole::cases()))],
            'department_id' => [
                Rule::requiredIf($role?->needsDepartment() ?? false),
                'nullable',
                'exists:departments,id',
            ],
        ]);

        $user->update([
            'first_name'    => $validated['first_name'],
            'middle_name'   => $validated['middle_name'] ?? null,
            'last_name'     => $validated['last_name'],
            'role'          => $validated['role'],
            'department_id' => $role?->needsDepartment() ? $validated['department_id'] : null,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$user->name}'s account has been updated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', "{$name} has been removed.");
    }

    public function resend(User $user): RedirectResponse
    {
        if ($user->isActivated()) {
            return back()->with('status', "{$user->name}'s account is already active.");
        }

        try {
            $this->sendInvitation($user);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['resend' => "Failed to send email: {$e->getMessage()}"]);
        }

        return back()->with('status', "Invitation resent to {$user->email}.");
    }

    public function getLink(User $user): RedirectResponse
    {
        if ($user->isActivated()) {
            return back()->with('status', "{$user->name}'s account is already active — no invite link needed.");
        }

        // Regenerate the token and flash the plain URL so admin can copy it manually.
        $plain    = $user->generateInvitationToken();
        $setupUrl = route('invitation.show', ['token' => $plain]);

        return back()->with([
            'invite_link'       => $setupUrl,
            'invite_link_for'   => $user->name,
        ]);
    }

    private function sendInvitation(User $user): void
    {
        $plain    = $user->generateInvitationToken();
        $setupUrl = route('invitation.show', ['token' => $plain]);

        Mail::to($user->email)->send(
            new StaffInvitationMail($user, $setupUrl, auth()->user()->name)
        );
    }
}
