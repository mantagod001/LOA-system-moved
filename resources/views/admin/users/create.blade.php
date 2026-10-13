@extends('layouts.staff')

@section('eyebrow', 'User Management')
@section('title', 'Invite New User')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
        <div class="border-b border-maroon-900/8 px-6 py-4">
            <p class="text-sm font-semibold text-maroon-950">Send Invitation</p>
            <p class="mt-0.5 text-xs text-maroon-900/50">
                An invitation email will be sent with a 48-hour setup link. The recipient sets their own password on first login.
            </p>
        </div>
        <div class="px-6 py-5">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="first_name" class="block text-xs font-medium text-maroon-900 mb-1">First name</label>
                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required
                               class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('first_name') border-red-400 bg-red-50 @enderror">
                        @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="middle_name" class="block text-xs font-medium text-maroon-900 mb-1">Middle name</label>
                        <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name') }}"
                               class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700">
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-medium text-maroon-900 mb-1">Last name</label>
                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required
                               class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('last_name') border-red-400 bg-red-50 @enderror">
                        @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="email" class="block text-xs font-medium text-maroon-900 mb-1">EVSU email</label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email') }}" required
                           placeholder="name@evsu.edu.ph"
                           class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('email') border-red-400 bg-red-50 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role + Department --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="role" class="block text-xs font-medium text-maroon-900 mb-1">Role</label>
                        <select id="role" name="role" required data-role-select
                                class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('role') border-red-400 @enderror">
                            <option value="" disabled @selected(! old('role'))>Select role…</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="hidden" data-department-wrap>
                        <label for="department_id" class="block text-xs font-medium text-maroon-900 mb-1">Department</label>
                        <select id="department_id" name="department_id"
                                class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('department_id') border-red-400 @enderror">
                            <option value="">Select department…</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-maroon-900/8 pt-4">
                    <a href="{{ route('admin.users.index') }}"
                       class="rounded-xl border border-maroon-900/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                        ← Back
                    </a>
                    <button type="submit"
                            class="rounded-xl bg-maroon-800 px-5 py-2 text-sm font-semibold text-cream-50 shadow-sm hover:bg-maroon-700">
                        Send Invitation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
