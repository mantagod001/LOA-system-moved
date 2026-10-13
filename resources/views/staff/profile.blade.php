@extends('layouts.staff')

@section('eyebrow', 'My Account')
@section('title', 'User Profile')

@section('content')
@php
    $nameParts = explode(' ', trim($user->name ?? 'U'));
    $initials  = strtoupper(($nameParts[0][0] ?? 'U') . (end($nameParts)[0] ?? ''));
    $roleColor = match ($user->role) {
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

    {{-- ── Left column: identity ──────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Identity card --}}
        <div class="overflow-hidden rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            {{-- Gradient banner --}}
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
                        <p class="truncate text-base font-bold text-maroon-950">{{ $user->name }}</p>
                        <p class="truncate text-xs text-maroon-900/55">{{ $user->email }}</p>
                    </div>
                    <span class="shrink-0 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $roleColor }}">
                        {{ $user->role->label() }}
                    </span>
                </div>

                <dl class="mt-4 space-y-2 border-t border-maroon-900/8 pt-4 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-maroon-900/50">Department</dt>
                        <dd class="text-xs font-medium text-maroon-950">{{ $user->department?->name ?? 'Campus-wide' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-xs text-maroon-900/50">Member since</dt>
                        <dd class="text-xs font-medium text-maroon-950">{{ $user->created_at?->format('M j, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

    </div>

    {{-- ── Right column: edit name ─────────────────────────────────────────── --}}
    <div class="space-y-5 lg:col-span-2">

        {{-- Edit name --}}
        <div class="rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
            <div class="border-b border-maroon-900/8 px-5 py-3.5">
                <p class="text-sm font-semibold text-maroon-950">Edit name</p>
                <p class="mt-0.5 text-xs text-maroon-900/50">Use first, middle, and last name. Email and role are managed by the administrator.</p>
            </div>
            <div class="px-5 py-4">
                @if (session('profile_status'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">
                        {{ session('profile_status') }}
                    </div>
                @endif
                <form method="POST" action="{{ route('staff.profile.name') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label for="first_name" class="block text-xs font-medium text-maroon-900 mb-1">First name</label>
                            <input id="first_name" type="text" name="first_name" required
                                   value="{{ old('first_name', $user->first_name) }}"
                                   class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('first_name') border-red-400 bg-red-50 @enderror">
                            @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="middle_name" class="block text-xs font-medium text-maroon-900 mb-1">Middle name</label>
                            <input id="middle_name" type="text" name="middle_name"
                                   value="{{ old('middle_name', $user->middle_name) }}"
                                   class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700">
                        </div>
                        <div>
                            <label for="last_name" class="block text-xs font-medium text-maroon-900 mb-1">Last name</label>
                            <input id="last_name" type="text" name="last_name" required
                                   value="{{ old('last_name', $user->last_name) }}"
                                   class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2.5 text-sm text-maroon-950 outline-none focus:ring-2 focus:ring-maroon-700 @error('last_name') border-red-400 bg-red-50 @enderror">
                            @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="rounded-xl bg-maroon-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-maroon-700">
                            Save name
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
