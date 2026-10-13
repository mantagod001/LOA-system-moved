<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('staff.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function updateName(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'  => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
        ]);

        $request->user()->update([
            'first_name'  => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name'   => $validated['last_name'],
        ]);

        return back()->with('profile_status', 'Name updated successfully.');
    }
}
