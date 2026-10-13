@extends('layouts.staff')

@section('eyebrow', 'User Management')
@section('title', 'Edit User')

@section('content')
@php
    $parts    = explode(' ', trim($invitee->name));
    $initials = strtoupper(($parts[0][0] ?? 'U') . (end($parts)[0] ?? ''));
    $roleColor = match ($invitee->role) {
        \App\Enums\UserRole::Administrator  => 'bg-purple-100 text-purple-800 border-purple-300',
        \App\Enums\UserRole::DepartmentHead => 'bg-blue-100 text-blue-800 border-blue-300',
        \App\Enums\UserRole::SasoOfficer    => 'bg-amber-100 text-amber-800 border-amber-300',
        \App\Enums\UserRole::CampusDirector => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        \App\Enums\UserRole::President      => 'bg-indigo-100 text-indigo-800 border-indigo-300',
        \App\Enums\UserRole::Registrar      => 'bg-teal-100 text-teal-800 border-teal-300',
        \App\Enums\UserRole::Guidance       => 'bg-sky-100 text-sky-800 border-sky-300',
        default                             => 'bg-gray-100 text-gray-700 border-gray-300',
    };
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

    {{-- ── Left: user identity card ────────────────────────────────────────── --}}
    <div>
        <div class="overflow-hidden rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            <div class="relative h-20 bg-gradient-to-r from-maroon-900 via-maroon-800 to-maroon-700">
                <div class="absolute -bottom-7 left-5">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full border-4 border-white bg-maroon-600 text-lg font-bold uppercase text-cream-50 shadow-md">
                        {{ $initials }}
                    </div>
                </div>
            </div>
            <div class="px-5 pb-5 pt-10">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="truncate text-base font-bold text-maroon-950">{{ $invitee->name }}</p>
                        <p class="truncate text-xs text-maroon-900/55">{{ $invitee->email }}</p>
                    </div>
                    <span class="shrink-0 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $roleColor }}">
                        {{ $invitee->role->label() }}
                    </span>
                </div>
                <dl class="mt-4 space-y-2 border-t border-maroon-900/8 pt-4">
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-maroon-900/50">Status</dt>
                        <dd>
                            @if ($invitee->isActivated())
                                <span class="rounded-full border border-emerald-600 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Active</span>
                            @else
                                <span class="rounded-full border border-amber-500 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Pending invite</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-maroon-900/50">Department</dt>
                        <dd class="text-xs font-medium text-maroon-950">{{ $invitee->department?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-maroon-900/50">Member since</dt>
                        <dd class="text-xs font-medium text-maroon-950">{{ $invitee->created_at?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- ── Right: edit form ────────────────────────────────────────────────── --}}
    <div class="space-y-5 lg:col-span-2">

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->has('role'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first('role') }}
            </div>
        @endif

        <div class="rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            <div class="border-b border-maroon-900/8 px-5 py-3.5">
                <p class="text-sm font-semibold text-maroon-950">Account Details</p>
                <p class="mt-0.5 text-xs text-maroon-900/50">Email address cannot be changed after account creation.</p>
            </div>
            <div class="px-5 py-5">
                <form method="POST" action="{{ route('admin.users.update', $invitee) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="first_name" class="block text-xs font-medium text-maroon-900 mb-1">First name</label>
                            <input id="first_name" name="first_name" type="text" required
                                   value="{{ old('first_name', $invitee->first_name) }}"
                                   class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('first_name') border-red-400 bg-red-50 @enderror">
                            @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="middle_name" class="block text-xs font-medium text-maroon-900 mb-1">Middle name</label>
                            <input id="middle_name" name="middle_name" type="text"
                                   value="{{ old('middle_name', $invitee->middle_name) }}"
                                   class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700">
                        </div>
                        <div>
                            <label for="last_name" class="block text-xs font-medium text-maroon-900 mb-1">Last name</label>
                            <input id="last_name" name="last_name" type="text" required
                                   value="{{ old('last_name', $invitee->last_name) }}"
                                   class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('last_name') border-red-400 bg-red-50 @enderror">
                            @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-maroon-900 mb-1">Email address</label>
                        <input type="text" value="{{ $invitee->email }}" disabled
                               class="w-full rounded-xl border border-maroon-900/10 bg-maroon-50 px-3 py-2.5 text-sm text-maroon-900/50 cursor-not-allowed">
                    </div>

                    {{-- Role + Department side by side --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="role" class="block text-xs font-medium text-maroon-900 mb-1">Role</label>
                            <select id="role" name="role" required data-role-select
                                    class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('role') border-red-400 @enderror">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}" @selected(old('role', $invitee->role->value) === $role->value)>{{ $role->label() }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div @class(['hidden' => ! $invitee->role->needsDepartment()]) data-department-wrap>
                            <label for="department_id" class="block text-xs font-medium text-maroon-900 mb-1">Department</label>
                            <select id="department_id" name="department_id"
                                    class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('department_id') border-red-400 @enderror">
                                <option value="">Select department</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id', $invitee->department_id) == $dept->id)>{{ $dept->name }}</option>
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
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
